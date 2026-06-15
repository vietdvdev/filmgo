<?php

namespace Database\Factories;

use App\Models\Seat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShowtimeSeat>
 */
class ShowtimeSeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'showtime_id' => Showtime::factory(),
            'seat_id' => Seat::factory(),
            'user_id' => null,
            'status' => 'available',
            'locked_at' => null,
            'expires_at' => null,
        ];
    }
}
