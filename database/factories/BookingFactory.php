<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'showtime_id' => Showtime::factory(),
            'booking_code' => 'FLM-' . fake()->unique()->numberBetween(100000, 999999),
            'total_amount' => 120000,
            'discount_amount' => 0,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expired_at' => now()->addMinutes(15),
        ];
    }
}
