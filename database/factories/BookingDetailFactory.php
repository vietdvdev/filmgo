<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\ShowtimeSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingDetail>
 */
class BookingDetailFactory extends Factory
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
            'showtime_seat_id' => ShowtimeSeat::factory(),
            'price' => 75000,
        ];
    }
}
