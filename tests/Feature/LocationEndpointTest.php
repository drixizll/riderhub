<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_rider_can_post_location_and_customer_can_read_it(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($rider)->post('/rider/locations', [
            'booking_id' => $booking->id,
            'lat' => 14.5580,
            'lng' => 121.0230,
        ])->assertOk();

        $this->actingAs($customer)
            ->get('/customer/bookings/' . $booking->id . '/location')
            ->assertOk();
    }

    public function test_unassigned_rider_cannot_update_a_booking_location(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create(['status' => 'accepted']);

        $this->actingAs($rider)->postJson('/rider/locations', [
            'booking_id' => $booking->id,
            'lat' => 14.5580,
            'lng' => 121.0230,
        ])->assertForbidden();

        $this->assertDatabaseCount('rider_locations', 0);
    }

    public function test_customer_cannot_read_another_customers_rider_location(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $owner = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create([
            'customer_id' => $owner->id,
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($customer)
            ->getJson('/customer/bookings/' . $booking->id . '/location')
            ->assertForbidden();
    }
}
