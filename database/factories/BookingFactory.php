<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);

        return [
            'customer_id' => $customer->id,
            'rider_id' => null,
            'type' => 'ride',
            'pickup_address' => 'Makati City',
            'pickup_lat' => 14.5547,
            'pickup_lng' => 121.0244,
            'dropoff_address' => 'BGC',
            'dropoff_lat' => 14.5507,
            'dropoff_lng' => 121.0491,
            'item_description' => null,
            'fare' => 150.00,
            'payment_method' => 'cash',
            'status' => 'pending',
        ];
    }
}
