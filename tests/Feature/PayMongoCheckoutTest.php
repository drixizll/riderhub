<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayMongoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_booking_redirects_to_paymongo_hosted_checkout(): void
    {
        config([
            'services.paymongo.secret_key' => 'sk_test_riderhub',
            'services.paymongo.webhook_secret' => 'whsec_riderhub',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_riderhub',
                    'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_test_riderhub'],
                ],
            ], 201),
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);

        $response = $this->actingAs($customer)->post('/customer/bookings', [
            'type' => 'ride',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'Bonifacio Global City',
            'dropoff_lat' => 14.5507,
            'dropoff_lng' => 121.0491,
            'payment_method' => 'online',
        ]);

        $response->assertRedirect('https://checkout.paymongo.com/cs_test_riderhub');
        $this->assertDatabaseHas('payments', [
            'method' => 'online',
            'status' => 'pending',
            'paymongo_reference' => 'cs_test_riderhub',
            'paymongo_checkout_url' => 'https://checkout.paymongo.com/cs_test_riderhub',
        ]);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame(0, $rider->notifications()->count());

        Http::assertSent(fn (ClientRequest $request): bool =>
            $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_riderhub:'))
            && $request['data']['attributes']['payment_method_types'] === ['card', 'gcash', 'paymaya']
            && $request['data']['attributes']['line_items'][0]['currency'] === 'PHP'
        );
    }

    public function test_online_booking_stays_unassigned_when_paymongo_is_not_configured(): void
    {
        config(['services.paymongo.secret_key' => null]);
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);

        $response = $this->actingAs($customer)->post('/customer/bookings', [
            'type' => 'ride',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'Bonifacio Global City',
            'dropoff_lat' => 14.5507,
            'dropoff_lng' => 121.0491,
            'payment_method' => 'online',
        ]);

        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('customer.bookings.show', $booking))
            ->assertSessionHas('error');
        $this->assertSame(0, $rider->notifications()->count());
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'pending',
            'paymongo_reference' => null,
        ]);
    }

    public function test_signed_paid_webhook_marks_the_exact_payment_and_dispatches_once(): void
    {
        config([
            'services.paymongo.webhook_secret' => 'whsec_riderhub',
            'services.paymongo.livemode' => false,
        ]);
        [$booking, $rider] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_paid',
            'paymongo_checkout_url' => 'https://checkout.paymongo.com/cs_test_paid',
        ]);
        $payload = $this->paidEventPayload($booking, $payment, 'cs_test_paid');

        $this->postSignedWebhook($payload)->assertOk();
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertSame(1, $rider->notifications()->count());
        $this->assertDatabaseCount('notifications', 2);

        $this->postSignedWebhook($payload)->assertOk();
        $this->assertSame(1, $rider->notifications()->count());
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_invalid_webhook_signature_cannot_mark_a_payment_paid(): void
    {
        config(['services.paymongo.webhook_secret' => 'whsec_riderhub']);
        [$booking] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_forged',
        ]);
        $payload = $this->paidEventPayload($booking, $payment, 'cs_test_forged');

        $this->call('POST', '/webhooks/paymongo', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => 't='.time().',te='.str_repeat('0', 64).',li='.str_repeat('1', 64),
        ], $payload)->assertUnauthorized();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }

    public function test_webhook_rejects_paid_events_with_a_mismatched_amount(): void
    {
        config(['services.paymongo.webhook_secret' => 'whsec_riderhub']);
        [$booking] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 150,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_wrong_amount',
        ]);
        $event = json_decode($this->paidEventPayload($booking, $payment, 'cs_test_wrong_amount'), true);
        $event['data']['attributes']['data']['attributes']['payments'][0]['attributes']['amount'] = 1;
        $payload = json_encode($event, JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertUnprocessable();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }

    public function test_customer_return_confirms_payment_by_retrieving_the_session_from_paymongo(): void
    {
        config([
            'services.paymongo.secret_key' => 'sk_test_riderhub',
            'services.paymongo.livemode' => false,
        ]);
        [$booking, $rider] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_return',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_return' => Http::response([
                'data' => [
                    'id' => 'cs_test_return',
                    'attributes' => [
                        'livemode' => false,
                        'reference_number' => 'RIDERHUB-'.$booking->id,
                        'metadata' => ['booking_id' => (string) $booking->id],
                        'payments' => [[
                            'id' => 'pay_test_return',
                            'attributes' => [
                                'amount' => (int) round($payment->amount * 100),
                                'currency' => 'PHP',
                                'status' => 'paid',
                            ],
                        ]],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($booking->customer)
            ->get(route('customer.bookings.show', $booking).'?checkout=return')
            ->assertRedirect(route('customer.bookings.show', $booking))
            ->assertSessionHas('success', 'Payment confirmed by PayMongo. Your request is now available to riders.');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertSame(1, $rider->notifications()->count());
        Http::assertSent(fn (ClientRequest $request): bool =>
            $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/checkout_sessions/cs_test_return'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_riderhub:'))
        );
    }

    public function test_rider_queue_does_not_show_unpaid_online_bookings(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $cashBooking = Booking::factory()->create(['payment_method' => 'cash']);
        Payment::factory()->create([
            'booking_id' => $cashBooking->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);
        $onlineBooking = Booking::factory()->create([
            'payment_method' => 'online',
            'pickup_address' => 'Unpaid online pickup',
        ]);
        Payment::factory()->create([
            'booking_id' => $onlineBooking->id,
            'method' => 'online',
            'status' => 'pending',
        ]);

        $this->actingAs($rider)
            ->get(route('rider.bookings.index'))
            ->assertOk()
            ->assertSee($cashBooking->pickup_address)
            ->assertDontSee('Unpaid online pickup');
    }

    public function test_expired_checkout_session_is_replaced_with_a_new_one(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_riderhub']);
        [$booking] = $this->makeOnlineBookingAndRider();
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_old',
            'paymongo_checkout_url' => 'https://checkout.paymongo.com/cs_test_old',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_old' => Http::response($this->sessionResponse($booking, 'cs_test_old', 'expired')),
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_new',
                    'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_test_new'],
                ],
            ], 201),
        ]);

        $this->actingAs($booking->customer)
            ->post(route('customer.bookings.checkout', $booking))
            ->assertRedirect('https://checkout.paymongo.com/cs_test_new');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'paymongo_reference' => 'cs_test_new',
        ]);
    }

    public function test_active_checkout_session_is_reused(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_riderhub']);
        [$booking] = $this->makeOnlineBookingAndRider();
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_active',
            'paymongo_checkout_url' => 'https://checkout.paymongo.com/cs_test_active',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_active' => Http::response($this->sessionResponse($booking, 'cs_test_active', 'active')),
        ]);

        $this->actingAs($booking->customer)
            ->post(route('customer.bookings.checkout', $booking))
            ->assertRedirect('https://checkout.paymongo.com/cs_test_active');

        Http::assertNotSent(fn (ClientRequest $request): bool => $request->method() === 'POST');
    }

    public function test_returning_without_paying_shows_a_friendly_message(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_riderhub']);
        [$booking, $rider] = $this->makeOnlineBookingAndRider();
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_unpaid',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_unpaid' => Http::response($this->sessionResponse($booking, 'cs_test_unpaid', 'active')),
        ]);

        $this->actingAs($booking->customer)
            ->get(route('customer.bookings.show', $booking).'?checkout=return')
            ->assertRedirect(route('customer.bookings.show', $booking))
            ->assertSessionHas('info');

        $this->assertSame(0, $rider->notifications()->count());
    }

    public function test_customer_can_cancel_an_unpaid_online_booking_and_the_session_is_expired(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_riderhub']);
        [$booking] = $this->makeOnlineBookingAndRider();
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_cancel',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_cancel/expire' => Http::response($this->sessionResponse($booking, 'cs_test_cancel', 'expired')),
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_cancel' => Http::response($this->sessionResponse($booking, 'cs_test_cancel', 'active')),
        ]);

        $this->actingAs($booking->customer)
            ->post(route('customer.bookings.cancel', $booking))
            ->assertRedirect(route('customer.bookings.index'));

        $this->assertSame('cancelled', $booking->fresh()->status);
        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/checkout_sessions/cs_test_cancel/expire');
    }

    public function test_cancel_does_not_cancel_a_booking_that_was_already_paid(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_riderhub', 'services.paymongo.livemode' => false]);
        [$booking, $rider] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_paid_cancel',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_paid_cancel' => Http::response($this->sessionResponse($booking, 'cs_test_paid_cancel', 'active', $payment)),
        ]);

        $this->actingAs($booking->customer)
            ->post(route('customer.bookings.cancel', $booking))
            ->assertRedirect(route('customer.bookings.show', $booking))
            ->assertSessionHas('success');

        $this->assertSame('pending', $booking->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(1, $rider->notifications()->count());
    }

    public function test_webhook_for_an_unknown_session_is_acknowledged(): void
    {
        config(['services.paymongo.webhook_secret' => 'whsec_riderhub', 'services.paymongo.livemode' => false]);
        [$booking] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_known',
        ]);

        $this->postSignedWebhook($this->paidEventPayload($booking, $payment, 'cs_test_other_app'))
            ->assertOk()
            ->assertJson(['ignored' => true]);

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_sync_command_confirms_payments_that_were_paid_on_paymongo(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_riderhub', 'services.paymongo.livemode' => false]);
        [$booking, $rider] = $this->makeOnlineBookingAndRider();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare,
            'method' => 'online',
            'paymongo_reference' => 'cs_test_sync',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_sync' => Http::response($this->sessionResponse($booking, 'cs_test_sync', 'active', $payment)),
        ]);

        $this->artisan('paymongo:sync')->assertSuccessful();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(1, $rider->notifications()->count());
    }

    private function sessionResponse(Booking $booking, string $sessionId, string $status, ?Payment $paidPayment = null): array
    {
        return [
            'data' => [
                'id' => $sessionId,
                'type' => 'checkout_session',
                'attributes' => [
                    'status' => $status,
                    'livemode' => false,
                    'checkout_url' => 'https://checkout.paymongo.com/'.$sessionId,
                    'reference_number' => 'RIDERHUB-'.$booking->id,
                    'metadata' => ['booking_id' => (string) $booking->id],
                    'payments' => $paidPayment ? [[
                        'id' => 'pay_test_'.$paidPayment->id,
                        'attributes' => [
                            'amount' => (int) round($paidPayment->amount * 100),
                            'currency' => 'PHP',
                            'status' => 'paid',
                        ],
                    ]] : [],
                ],
            ],
        ];
    }

    private function makeOnlineBookingAndRider(): array
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create([
            'payment_method' => 'online',
            'fare' => 150,
        ]);

        return [$booking, $rider];
    }

    private function paidEventPayload(Booking $booking, Payment $payment, string $sessionId): string
    {
        return json_encode([
            'data' => [
                'id' => 'evt_test_'.$payment->id,
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'livemode' => false,
                    'data' => [
                    'id' => $sessionId,
                    'attributes' => [
                        'reference_number' => 'RIDERHUB-'.$booking->id,
                        'metadata' => ['booking_id' => (string) $booking->id],
                        'payments' => [[
                            'id' => 'pay_test_'.$payment->id,
                            'attributes' => [
                                'amount' => (int) round($payment->amount * 100),
                                'currency' => 'PHP',
                                'status' => 'paid',
                            ],
                        ]],
                    ],
                ],
            ],
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function postSignedWebhook(string $payload)
    {
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_riderhub');

        return $this->call('POST', '/webhooks/paymongo', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => 't='.$timestamp.',te='.$signature.',li='.str_repeat('0', 64),
        ], $payload);
    }
}
