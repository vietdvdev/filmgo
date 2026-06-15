<?php

namespace Database\Factories;

use App\Models\PriceRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceRule>
 */
class PriceRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Giờ Vàng Cuối Tuần', 'Thứ 2 Đồng Giá', 'Khung Giờ Đêm', 'Suất Chiếu Sớm']),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '18:00:00',
            'end_time' => '22:00:00',
            'adjustment_amount' => fake()->randomElement([10000, 15000, 20000, -10000]),
            'is_active' => 1,
        ];
    }
}
