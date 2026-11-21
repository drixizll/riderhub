<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_a_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post('/customer/bookings', [
            'type' => 'ride',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'Bonifacio Global City',
            'dropoff_lat' => 14.5507,
            'dropoff_lng' => 121.0491,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect('/customer/bookings');
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'type' => 'ride',
            'status' => 'pending',
        ]);
    }

    public function test_customer_can_view_their_booking_details(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)
            ->get('/customer/bookings/' . $booking->id)
            ->assertOk();
    }

    public function test_customer_can_view_the_booking_creation_form(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get('/customer/bookings/create')
            ->assertOk()
            ->assertSee('booking-map')
            ->assertSee('Use my current location')
            ->assertSee('Estimated fare');
    }

    public function test_customer_can_view_live_tracking_for_an_active_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($customer)
            ->get('/customer/bookings/' . $booking->id)
            ->assertOk()
            ->assertSee('Live rider tracking')
            ->assertSee('tracking-map');
    }

    public function test_delivery_requires_an_item_description(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->from('/customer/bookings/create')->post('/customer/bookings', [
            'type' => 'delivery',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'Bonifacio Global City',
            'dropoff_lat' => 14.5507,
            'dropoff_lng' => 121.0491,
            'payment_method' => 'cash',
        ])->assertSessionHasErrors('item_description');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_rider_can_accept_an_open_booking_and_advance_its_status(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create(['status' => 'pending', 'rider_id' => null]);
        $customer = $booking->customer;
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $this->actingAs($rider)
            ->get('/rider/dashboard')
            ->assertOk()
            ->assertSee('Open booking requests');

        $this->post(route('rider.bookings.accept', $booking))
            ->assertRedirect(route('rider.dashboard'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);
        $notificationTitles = $customer->notifications()
            ->get()
            ->map(fn ($notification) => $notification->data['title'])
            ->all();
        $this->assertContains('Rider matched', $notificationTitles);

        $this->post(route('rider.bookings.status', $booking), ['status' => 'picked_up'])
            ->assertRedirect(route('rider.dashboard'));
        $this->post(route('rider.bookings.status', $booking), ['status' => 'completed'])
            ->assertRedirect(route('rider.dashboard'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'paid',
        ]);
        $notificationTitles = $customer->notifications()
            ->get()
            ->map(fn ($notification) => $notification->data['title'])
            ->all();
        $this->assertContains('Trip completed', $notificationTitles);
    }

    public function test_rider_cannot_accept_a_second_booking_while_one_is_active(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        Booking::factory()->create(['rider_id' => $rider->id, 'status' => 'accepted']);
        $openBooking = Booking::factory()->create(['rider_id' => null, 'status' => 'pending']);

        $this->actingAs($rider)
            ->post(route('rider.bookings.accept', $openBooking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'id' => $openBooking->id,
            'rider_id' => null,
            'status' => 'pending',
        ]);
    }

    public function test_rider_cannot_skip_a_booking_status_transition(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $booking = Booking::factory()->create([
            'rider_id' => $rider->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($rider)
            ->post(route('rider.bookings.status', $booking), ['status' => 'completed'])
            ->assertStatus(422);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'accepted',
        ]);
    }

    public function test_admin_can_review_and_filter_platform_bookings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingBooking = Booking::factory()->create(['status' => 'pending', 'type' => 'delivery']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Operations overview')
            ->assertSee('Platform users');

        $this->get('/admin/bookings?status=pending&type=delivery')
            ->assertOk()
            ->assertSee($pendingBooking->pickup_address)
            ->assertSee('Booking records');
    }

    public function test_admin_can_search_the_people_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'name' => 'Directory Customer',
            'role' => 'customer',
            'email' => 'directory@example.test',
        ]);
        User::factory()->create(['name' => 'Another Rider', 'role' => 'rider']);

        $this->actingAs($admin)
            ->get('/admin/users?role=customer&search=Directory')
            ->assertOk()
            ->assertSee($customer->name)
            ->assertDontSee('Another Rider');
    }

    public function test_admin_pages_are_not_available_to_customers(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_booking_rejects_identical_pickup_and_dropoff_coordinates(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->from('/customer/bookings/create')->post('/customer/bookings', [
            'type' => 'ride',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'Same Spot',
            'dropoff_lat' => 14.5547,
            'dropoff_lng' => 121.0244,
            'payment_method' => 'cash',
        ])->assertSessionHasErrors('dropoff_address');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cancelling_a_pending_cash_booking_also_cancels_its_payment(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->post(route('customer.bookings.cancel', $booking))
            ->assertRedirect(route('customer.bookings.index'));

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame('cancelled', $payment->fresh()->status);
    }

    public function test_admin_can_update_and_delete_a_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'customer', 'name' => 'Old Name']);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $target), [
                'name' => 'Updated Rider',
                'email' => 'Updated@Example.com',
                'phone' => '09171234567',
                'role' => 'rider',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Rider',
            'email' => 'updated@example.com',
            'role' => 'rider',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertNull($target->fresh());

        // Admin cannot delete themselves
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');
        $this->assertNotNull($admin->fresh());
    }

    public function test_customer_can_edit_pending_unpaid_booking_locations_and_fare_recalculates(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'status' => 'pending',
            'fare' => 80.00,
        ]);
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'method' => 'cash',
            'status' => 'pending',
            'amount' => 80.00,
        ]);

        $this->actingAs($customer)
            ->patch(route('customer.bookings.update', $booking), [
                'pickup_address' => 'Tagoloan, Misamis Oriental',
                'pickup_lat' => 8.5378,
                'pickup_lng' => 124.7536,
                'dropoff_address' => 'Cagayan de Oro City',
                'dropoff_lat' => 8.4810,
                'dropoff_lng' => 124.6498,
            ])
            ->assertRedirect(route('customer.bookings.show', $booking))
            ->assertSessionHas('success');

        $booking->refresh();
        $payment->refresh();
        $this->assertSame('Tagoloan, Misamis Oriental', $booking->pickup_address);
        $this->assertGreaterThan(80.00, $booking->fare);
        $this->assertSame($booking->fare, $payment->amount);
    }
}
