<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripChatAndRadarTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_assigned_rider_can_exchange_trip_messages(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);
        $outsider = User::factory()->create(['role' => 'customer']);

        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);

        // Customer sends message
        $this->actingAs($customer)
            ->postJson(route('bookings.messages.store', $booking), ['body' => 'Nasa pickup na po ako'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Nasa pickup na po ako')
            ->assertJsonPath('message.is_mine', true);

        // Rider fetches and replies
        $this->actingAs($rider)
            ->getJson(route('bookings.messages.index', $booking))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.is_mine', false);

        $this->actingAs($rider)
            ->postJson(route('bookings.messages.store', $booking), ['body' => 'Papunta na po!'])
            ->assertCreated();

        // Outsider is blocked
        $this->actingAs($outsider)
            ->getJson(route('bookings.messages.index', $booking))
            ->assertForbidden();
    }

    public function test_rider_can_poll_open_booking_requests(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);

        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => null,
            'status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $this->actingAs($rider)
            ->getJson(route('rider.requests.poll'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('latest_id', $booking->id);
    }

    public function test_api_enforces_single_active_trip_and_supports_cancel_and_accept(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);

        $customerToken = $this->postJson('/api/v1/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $riderToken = $this->postJson('/api/v1/login', [
            'email' => $rider->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $payload = [
            'type' => 'ride',
            'pickup_address' => 'Poblacion, Tagoloan',
            'pickup_lat' => 8.5378,
            'pickup_lng' => 124.7536,
            'dropoff_address' => 'Baluarte, Tagoloan',
            'dropoff_lat' => 8.5450,
            'dropoff_lng' => 124.7640,
            'payment_method' => 'cash',
        ];

        $firstId = $this->withToken($customerToken)
            ->postJson('/api/v1/bookings', $payload)
            ->assertCreated()
            ->json('booking.id');

        // Second booking while first is active must be rejected
        $this->withToken($customerToken)
            ->postJson('/api/v1/bookings', $payload)
            ->assertUnprocessable();

        // Rider accepts atomically via API
        $this->withToken($riderToken)
            ->postJson("/api/v1/bookings/{$firstId}/accept")
            ->assertOk()
            ->assertJsonPath('booking.status', 'accepted');

        // Second booking by another customer can be cancelled while pending
        $customer2 = User::factory()->create(['role' => 'customer']);
        $customer2Token = $this->postJson('/api/v1/login', [
            'email' => $customer2->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $secondId = $this->withToken($customer2Token)
            ->postJson('/api/v1/bookings', $payload)
            ->assertCreated()
            ->json('booking.id');

        $this->withToken($customer2Token)
            ->postJson("/api/v1/bookings/{$secondId}/cancel")
            ->assertOk()
            ->assertJsonPath('booking.status', 'cancelled');
    }
}
