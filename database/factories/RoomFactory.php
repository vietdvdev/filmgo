<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cinema_id' => Cinema::factory(),
            'room_name' => 'Phòng ' . fake()->numberBetween(1, 8),
            'capacity' => fake()->randomElement([60, 80, 100, 120]),
            'room_type' => fake()->randomElement(['2D', '3D', 'IMAX', '4DX']),
            'status' => 'active',
        ];
    }
}
