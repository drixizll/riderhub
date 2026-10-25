<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => 150.00,
            'method' => 'cash',
            'status' => 'pending',
            'paymongo_reference' => null,
            'paymongo_checkout_url' => null,
            'paid_at' => null,
        ];
    }
}
