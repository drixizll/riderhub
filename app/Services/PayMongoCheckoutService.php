<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoCheckoutService
{
    /**
     * PayMongo's Checkout Session API lives under /v1 (create, retrieve and expire).
     */
    private const API_VERSION = 'v1';

    public function __construct(private readonly BookingNotificationService $notifications) {}

    public function isConfigured(): bool
    {
        $secretKey = config('services.paymongo.secret_key');

        return is_string($secretKey) && $secretKey !== '';
    }

    /**
     * Returns a checkout URL for the booking. Re-uses the existing PayMongo session while it is
     * still usable, and transparently replaces it when it has expired.
     */
    public function createOrReuseSession(Booking $booking): string
    {
        if (! $this->isConfigured()) {
            throw new PayMongoCheckoutException('Online payments are not configured yet. Please choose cash or contact support.');
        }

        return DB::transaction(function () use ($booking): string {
            $payment = Payment::query()
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'paid') {
                throw new PayMongoCheckoutException('This booking has already been paid.');
            }

            if ($payment->method !== 'online' || $booking->status !== 'pending') {
                throw new PayMongoCheckoutException('Checkout is only available for a pending online booking.');
            }

            if ($payment->paymongo_reference && $payment->paymongo_checkout_url) {
                $session = $this->retrieveSession($payment->paymongo_reference);

                // If PayMongo cannot be reached, fall back to the stored link instead of failing.
                if ($session === null) {
                    return $payment->paymongo_checkout_url;
                }

                if ($this->paidAttempts($session) !== []) {
                    throw new PayMongoCheckoutException('PayMongo already received a payment for this booking. Refresh the page to see the confirmation.');
                }

                if (data_get($session, 'attributes.status') !== 'expired') {
                    return $payment->paymongo_checkout_url;
                }
            }

            [$sessionId, $checkoutUrl] = $this->createSession($booking, $payment);

            $payment->update([
                'paymongo_reference' => $sessionId,
                'paymongo_checkout_url' => $checkoutUrl,
            ]);

            return $checkoutUrl;
        });
    }

    public function hasValidWebhookSignature(string $payload, ?string $header): bool
    {
        $secret = config('services.paymongo.webhook_secret');

        if (! is_string($secret) || $secret === '' || ! is_string($header) || $header === '') {
            return false;
        }

        $parts = [];
        foreach (preg_split('/[,;]/', $header) ?: [] as $part) {
            if (str_contains($part, '=')) {
                [$key, $value] = array_map('trim', explode('=', $part, 2));
                $parts[strtolower($key)] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts[(bool) config('services.paymongo.livemode') ? 'li' : 'te'] ?? null;

        if (
            ! is_string($timestamp)
            || ! ctype_digit($timestamp)
            || ! is_string($signature)
            || ! preg_match('/\A[a-f0-9]{64}\z/i', $signature)
            || abs(time() - (int) $timestamp) > 300
        ) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;

        return hash_equals(hash_hmac('sha256', $signedPayload, $secret), strtolower($signature));
    }

    public function confirmPaidSession(Payment $payment, array $session, bool $livemode): bool
    {
        $booking = $payment->booking;
        $attributes = data_get($session, 'attributes');
        $paidPayments = $this->paidAttempts($session);

        if (
            data_get($session, 'id') !== $payment->paymongo_reference
            || data_get($attributes, 'reference_number') !== 'RIDERHUB-'.$booking->id
            || (string) data_get($attributes, 'metadata.booking_id') !== (string) $booking->id
            || count($paidPayments) !== 1
            || ! is_array($paidPayments[0] ?? null)
            || (int) data_get($paidPayments[0], 'attributes.amount') !== $this->amountInCentavos($payment)
            || strtoupper((string) data_get($paidPayments[0], 'attributes.currency')) !== 'PHP'
            || $livemode !== (bool) config('services.paymongo.livemode')
        ) {
            Log::warning('PayMongo paid session did not match its RiderHub booking.', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'checkout_session_id' => $payment->paymongo_reference,
            ]);

            return false;
        }

        return DB::transaction(function () use ($payment): bool {
            $lockedPayment = Payment::query()->lockForUpdate()->with('booking.customer')->findOrFail($payment->id);

            if ($lockedPayment->status === 'paid') {
                return true;
            }

            if ($lockedPayment->status !== 'pending') {
                return false;
            }

            $lockedPayment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $booking = $lockedPayment->booking;
            $booking->setRelation('payment', $lockedPayment);

            $this->notifications->notifyCustomerPaymentReceived($booking);
            // Only notifies riders while the booking is still pending.
            $this->notifications->notifyAvailableRiders($booking);

            return true;
        });
    }

    /**
     * Asks PayMongo for the latest session state and marks the payment as paid if it was.
     */
    public function retrieveAndConfirmPaidSession(Booking $booking): bool
    {
        $payment = $booking->payment;

        if (
            ! $this->isConfigured()
            || ! $payment
            || $payment->method !== 'online'
            || $payment->status !== 'pending'
            || ! $payment->paymongo_reference
        ) {
            return false;
        }

        $session = $this->retrieveSession($payment->paymongo_reference, ['booking_id' => $booking->id, 'payment_id' => $payment->id]);

        if ($session === null || $this->paidAttempts($session) === []) {
            return false;
        }

        return $this->confirmPaidSession(
            $payment,
            $session,
            (bool) data_get($session, 'attributes.livemode', false),
        );
    }

    /**
     * Expires an unpaid checkout session so the customer can safely cancel the booking.
     *
     * @return bool true when the booking can be cancelled (no payment was captured)
     */
    public function expireUnpaidSession(Payment $payment): bool
    {
        if (! $payment->paymongo_reference || ! $this->isConfigured()) {
            return true;
        }

        $session = $this->retrieveSession($payment->paymongo_reference, ['payment_id' => $payment->id]);

        if ($session === null) {
            // Unknown state: never cancel a booking that might have been paid.
            return false;
        }

        if ($this->paidAttempts($session) !== []) {
            return false;
        }

        if (data_get($session, 'attributes.status') === 'expired') {
            return true;
        }

        try {
            $response = $this->client(10)->post('/checkout_sessions/'.rawurlencode($payment->paymongo_reference).'/expire');
        } catch (ConnectionException) {
            return false;
        }

        if (! $response->successful()) {
            Log::warning('PayMongo checkout session could not be expired.', [
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
                'error_code' => $response->json('errors.0.code'),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null the checkout session resource, or null if it could not be retrieved
     */
    public function retrieveSession(string $sessionId, array $logContext = []): ?array
    {
        try {
            $response = $this->client(10)->get('/checkout_sessions/'.rawurlencode($sessionId));
        } catch (ConnectionException) {
            Log::warning('PayMongo checkout session status could not be retrieved.', $logContext + ['checkout_session_id' => $sessionId]);

            return null;
        }

        if (! $response->successful() || ! is_array($response->json('data'))) {
            Log::warning('PayMongo checkout session status request failed.', $logContext + [
                'checkout_session_id' => $sessionId,
                'http_status' => $response->status(),
                'error_code' => $response->json('errors.0.code'),
            ]);

            return null;
        }

        return $response->json('data');
    }

    /**
     * @return array{0: string, 1: string} [session id, checkout url]
     */
    private function createSession(Booking $booking, Payment $payment): array
    {
        $booking->loadMissing('customer');
        $bookingUrl = route('customer.bookings.show', $booking);

        $attributes = [
            'line_items' => [[
                'name' => $booking->type === 'delivery' ? 'RiderHub delivery' : 'RiderHub ride',
                'amount' => $this->amountInCentavos($payment),
                'currency' => 'PHP',
                'quantity' => 1,
            ]],
            'payment_method_types' => config('services.paymongo.payment_method_types'),
            'description' => 'RiderHub booking #'.$booking->id,
            'success_url' => $bookingUrl.'?checkout=return',
            'cancel_url' => $bookingUrl.'?checkout=return',
            'reference_number' => 'RIDERHUB-'.$booking->id,
            'send_email_receipt' => true,
            'show_description' => true,
            'show_line_items' => true,
            'metadata' => [
                'booking_id' => (string) $booking->id,
                'customer_id' => (string) $booking->customer_id,
            ],
        ];

        if ($booking->customer?->name && filter_var($booking->customer->email, FILTER_VALIDATE_EMAIL)) {
            $attributes['billing'] = [
                'name' => $booking->customer->name,
                'email' => $booking->customer->email,
            ];
        }

        try {
            $response = $this->client(20)->asJson()->post('/checkout_sessions', [
                'data' => ['attributes' => $attributes],
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('PayMongo checkout session request could not reach the provider.', [
                'booking_id' => $booking->id,
            ]);

            throw new PayMongoCheckoutException('PayMongo could not be reached. Please try checkout again in a moment.', previous: $exception);
        }

        $sessionId = $response->json('data.id');
        $checkoutUrl = $response->json('data.attributes.checkout_url');

        if (! $response->successful()) {
            Log::warning('PayMongo checkout session creation failed.', [
                'booking_id' => $booking->id,
                'http_status' => $response->status(),
                'error_code' => $response->json('errors.0.code'),
                'error_detail' => $response->json('errors.0.detail'),
            ]);

            throw new PayMongoCheckoutException('PayMongo could not start checkout right now. Please try again in a moment.');
        }

        if (
            ! is_string($sessionId)
            || $sessionId === ''
            || ! is_string($checkoutUrl)
            || parse_url($checkoutUrl, PHP_URL_SCHEME) !== 'https'
            || parse_url($checkoutUrl, PHP_URL_HOST) !== 'checkout.paymongo.com'
        ) {
            Log::warning('PayMongo returned an invalid checkout session response.', [
                'booking_id' => $booking->id,
                'http_status' => $response->status(),
            ]);

            throw new PayMongoCheckoutException('PayMongo returned an invalid checkout link. Please try again or contact support.');
        }

        return [$sessionId, $checkoutUrl];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function paidAttempts(array $session): array
    {
        $payments = data_get($session, 'attributes.payments');

        return is_array($payments)
            ? array_values(array_filter($payments, fn ($attempt) => data_get($attempt, 'attributes.status') === 'paid'))
            : [];
    }

    private function amountInCentavos(Payment $payment): int
    {
        return (int) round($payment->amount * 100);
    }

    private function client(int $timeout): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.paymongo.base_url'), '/').'/'.self::API_VERSION)
            ->withBasicAuth((string) config('services.paymongo.secret_key'), '')
            ->acceptJson()
            ->timeout($timeout);
    }
}
