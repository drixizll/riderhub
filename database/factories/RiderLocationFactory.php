<?php

namespace Database\Factories;

use App\Models\RiderLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiderLocation>
 */
class RiderLocationFactory extends Factory
{
    protected $model = RiderLocation::class;

    public function definition(): array
    {
        return [
            'rider_id' => User::factory()->create(['role' => 'rider'])->id,
            'booking_id' => null,
            'lat' => 14.5560,
            'lng' => 121.0270,
        ];
    }
}
