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
        $sentId = $this->actingAs($customer)
            ->postJson(route('bookings.messages.store', $booking), ['body' => 'Nasa pickup na po ako'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Nasa pickup na po ako')
            ->assertJsonPath('message.is_mine', true)
            ->json('message.id');

        // Rider fetches (marks customer's message as seen!) and replies
        $this->actingAs($rider)
            ->getJson(route('bookings.messages.index', $booking))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.is_mine', false)
            ->assertJsonPath('partner.is_online', true)
            ->assertJsonPath('partner.status_text', 'Active now');

        // Customer polls and sees Messenger "Seen" receipt up to their sent message ID
        $this->actingAs($customer)
            ->getJson(route('bookings.messages.index', $booking))
            ->assertOk()
            ->assertJsonPath('seen_up_to_id', $sentId);

        // Customer sends a photo attachment + emoji
        \Illuminate\Support\Facades\Storage::fake('public');
        $photo = \Illuminate\Http\UploadedFile::fake()->image('pickup-landmark.jpg', 400, 300);
        $photoRes = $this->actingAs($customer)
            ->post(route('bookings.messages.store', $booking), [
                'body' => 'Nandito po ako 📍',
                'image' => $photo,
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $this->assertNotNull($photoRes->json('message.image_url'));

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

    public function test_rider_availability_toggle_and_kyc_verification_guard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider', 'is_online' => true, 'rider_status' => 'verified']);

        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => null,
            'status' => 'pending',
            'payment_method' => 'cash',
        ]);

        // Rider toggles offline
        $this->actingAs($rider)
            ->post(route('rider.availability.toggle'))
            ->assertRedirect();

        $this->assertFalse($rider->fresh()->is_online);

        // Offline rider cannot accept booking
        $this->actingAs($rider)
            ->post(route('rider.bookings.accept', $booking))
            ->assertRedirect(route('rider.dashboard'))
            ->assertSessionHas('error');

        $this->assertNull($booking->fresh()->rider_id);

        // Rider toggles back online, then Admin sets rider to pending KYC + vehicle details
        $this->actingAs($rider)->post(route('rider.availability.toggle'))->assertRedirect();
        $this->assertTrue($rider->fresh()->is_online);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $rider), [
                'name' => $rider->name,
                'email' => $rider->email,
                'phone' => $rider->phone,
                'role' => 'rider',
                'rider_status' => 'pending',
                'vehicle_model' => 'Honda Click 125i',
                'plate_number' => 'ABC 1234',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('pending', $rider->fresh()->rider_status);
        $this->assertSame('Honda Click 125i', $rider->fresh()->vehicle_model);

        // Pending KYC rider cannot accept booking
        $this->actingAs($rider)
            ->post(route('rider.bookings.accept', $booking))
            ->assertRedirect(route('rider.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_promo_code_discount_and_cancellation_reason_work(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->post(route('customer.bookings.store'), [
                'type' => 'ride',
                'pickup_address' => 'Poblacion, Tagoloan',
                'pickup_lat' => 8.5378,
                'pickup_lng' => 124.7536,
                'dropoff_address' => 'Cagayan de Oro City',
                'dropoff_lat' => 8.4810,
                'dropoff_lng' => 124.6498,
                'payment_method' => 'cash',
                'promo_code' => 'TAGOLOAN20',
            ])
            ->assertRedirect(route('customer.bookings.index'));

        $booking = Booking::query()->latest('id')->firstOrFail();
        $this->assertSame('TAGOLOAN20', $booking->promo_code);
        $this->assertEquals(20.0, (float) $booking->discount_amount);

        // Cancel with a reason
        $this->actingAs($customer)
            ->post(route('customer.bookings.cancel', $booking), [
                'cancellation_reason' => 'Changed my mind',
            ])
            ->assertRedirect(route('customer.bookings.index'));

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame('Changed my mind', $booking->fresh()->cancellation_reason);
    }
}
