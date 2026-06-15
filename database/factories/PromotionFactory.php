<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->randomElement(['SALE10', 'SALE20', 'FILMGO50', 'HE2026', 'KHACHQUEN', 'STUDENT']);
        $discountType = fake()->randomElement(['percent', 'fixed']);
        $discountValue = $discountType === 'percent' ? fake()->randomElement([10, 15, 20, 50]) : fake()->randomElement([20000, 30000, 50000]);

        return [
            'code' => $code,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'min_order_amount' => fake()->randomElement([0, 100000, 150000]),
            'max_uses_per_user' => 1,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'quantity' => fake()->randomElement([null, 50, 100]),
            'status' => 'active',
        ];
    }
}
