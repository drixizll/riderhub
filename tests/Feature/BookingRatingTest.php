<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_rate_the_assigned_rider_once_after_a_completed_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'status' => 'completed',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.bookings.show', $booking))
            ->assertOk()
            ->assertSee('How was your rider?');

        $this->post(route('customer.bookings.rating', $booking), [
            'score' => 5,
            'comment' => 'Great trip.',
        ])->assertRedirect(route('customer.bookings.show', $booking));

        $this->assertDatabaseHas('booking_ratings', [
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'score' => 5,
        ]);
        $this->assertSame(1, $rider->ratingsReceived()->count());
        $this->assertSame(1, $rider->unreadNotifications()->count());

        $this->post(route('customer.bookings.rating', $booking), ['score' => 4])
            ->assertStatus(422);

        $this->assertDatabaseCount('booking_ratings', 1);
    }

    public function test_customers_cannot_rate_other_users_or_unfinished_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $anotherCustomer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);
        $completedBooking = Booking::factory()->create([
            'customer_id' => $anotherCustomer->id,
            'rider_id' => $rider->id,
            'status' => 'completed',
        ]);
        $activeBooking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($customer)
            ->post(route('customer.bookings.rating', $completedBooking), ['score' => 5])
            ->assertForbidden();

        $this->post(route('customer.bookings.rating', $activeBooking), ['score' => 5])
            ->assertStatus(422);

        $this->assertDatabaseCount('booking_ratings', 0);
    }
}
