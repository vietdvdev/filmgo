<?php

namespace Database\Factories;

use App\Models\SeatType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeatType>
 */
class SeatTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Thường', 'VIP', 'Sweetbox']),
            'surcharge_price' => fake()->randomElement([0, 20000, 40000]),
        ];
    }
}
