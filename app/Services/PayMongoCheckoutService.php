<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PayMongoCheckoutService
{
    public function __construct(private readonly BookingNotificationService $notifications) {}

    public function createOrReuseSession(Booking $booking): string
    {
        $secretKey = config('services.paymongo.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new PayMongoCheckoutException('Online payments are not configured yet. Please choose cash or contact support.');
        }

        return DB::transaction(function () use ($booking, $secretKey): string {
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
                return $payment->paymongo_checkout_url;
            }

            $booking->loadMissing('customer');
            $bookingUrl = route('customer.bookings.show', $booking);
            try {
                $response = Http::baseUrl(rtrim(config('services.paymongo.base_url'), '/'))
                    ->withBasicAuth($secretKey, '')
                    ->acceptJson()
                    ->asJson()
                    ->timeout(20)
                    ->post('/v2/checkout_sessions', [
                        'data' => [
                            'attributes' => [
                                'line_items' => [[
                                    'name' => $booking->type === 'delivery' ? 'RiderHub delivery' : 'RiderHub ride',
                                    'amount' => (int) round($payment->amount * 100),
                                    'currency' => 'PHP',
                                    'quantity' => 1,
                                ]],
                                'payment_method_types' => config('services.paymongo.payment_method_types'),
                                'success_url' => $bookingUrl.'?checkout=return',
                                'cancel_url' => $bookingUrl.'?checkout=return',
                                'reference_number' => 'RIDERHUB-'.$booking->id,
                                'send_email_receipt' => true,
                                'metadata' => [
                                    'booking_id' => (string) $booking->id,
                                    'customer_id' => (string) $booking->customer_id,
                                ],
                            ],
                        ],
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
        $payments = data_get($attributes, 'payments');
        $paidPayments = is_array($payments)
            ? array_values(array_filter($payments, fn ($attempt) => data_get($attempt, 'attributes.status') === 'paid'))
            : [];

        if (
            data_get($session, 'id') !== $payment->paymongo_reference
            || data_get($attributes, 'reference_number') !== 'RIDERHUB-'.$booking->id
            || (string) data_get($attributes, 'metadata.booking_id') !== (string) $booking->id
            || count($paidPayments) !== 1
            || ! is_array($paidPayments[0] ?? null)
            || (int) data_get($paidPayments[0], 'attributes.amount') !== (int) round($payment->amount * 100)
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
            $this->notifications->notifyCustomerPaymentReceived($booking);
            $this->notifications->notifyAvailableRiders($booking);

            return true;
        });
    }

    public function retrieveAndConfirmPaidSession(Booking $booking): bool
    {
        $secretKey = config('services.paymongo.secret_key');
        $payment = $booking->payment;

        if (
            ! is_string($secretKey)
            || $secretKey === ''
            || ! $payment
            || $payment->method !== 'online'
            || $payment->status !== 'pending'
            || ! $payment->paymongo_reference
        ) {
            return false;
        }

        try {
            $response = Http::baseUrl(rtrim(config('services.paymongo.base_url'), '/'))
                ->withBasicAuth($secretKey, '')
                ->acceptJson()
                ->timeout(10)
                ->get('/v2/checkout_sessions/'.rawurlencode($payment->paymongo_reference));
        } catch (ConnectionException) {
            Log::warning('PayMongo checkout session status could not be retrieved.', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('PayMongo checkout session status request failed.', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
            ]);

            return false;
        }

        return $this->confirmPaidSession(
            $payment,
            $response->json('data', []),
            (bool) $response->json('data.attributes.livemode', false),
        );
    }
}
