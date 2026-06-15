<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'transaction_code' => 'TXN-' . Str::upper(Str::random(8)),
            'amount' => 120000,
            'payment_method' => fake()->randomElement(['Credit', 'Momo', 'VNPay', 'ZaloPay']),
            'payment_status' => 'success',
            'paid_at' => now(),
        ];
    }
}
