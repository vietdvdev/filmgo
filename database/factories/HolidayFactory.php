<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Tết Nguyên Đán', 'Giải phóng Miền Nam', 'Quốc tế Lao động', 'Quốc khánh 2/9', 'Tết Dương Lịch']),
            'holiday_date' => fake()->unique()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'description' => 'Áp dụng khung giá vé đặc biệt ngày lễ',
        ];
    }
}
