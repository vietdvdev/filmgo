<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\SeatType;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seat>
 */
class SeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'seat_type_id' => SeatType::factory(),
            'seat_row' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']),
            'seat_number' => fake()->numberBetween(1, 12),
            'status' => 'active',
        ];
    }
}
