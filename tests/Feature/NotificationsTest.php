<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RiderHubNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_booking_notifies_riders_and_administrators_and_can_be_marked_read(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'type' => 'ride',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'BGC',
            'dropoff_lat' => 14.5507,
            'dropoff_lng' => 121.0491,
            'payment_method' => 'cash',
        ])->assertRedirect(route('customer.bookings.index'));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $rider->id,
            'type' => RiderHubNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => RiderHubNotification::class,
        ]);

        $notification = $rider->notifications()->firstOrFail();
        $this->actingAs($rider)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('New booking request');

        $this->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('rider.bookings.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_users_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $otherUser = User::factory()->create(['role' => 'customer']);
        $owner->notify(new RiderHubNotification(
            'Private update',
            'Only the notification owner can read this.',
            route('customer.dashboard', absolute: false),
        ));

        $notification = $owner->notifications()->firstOrFail();

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
