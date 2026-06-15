<?php

namespace Database\Factories;

use App\Models\Combo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Combo>
 */
class ComboFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $combos = [
            [
                'combo_name' => 'Combo Single (1 Bắp lớn + 1 Nước ngọt)',
                'price' => 75000,
                'description' => '1 bắp lớn vị caramel/phô mai + 1 ly Pepsi lớn.',
            ],
            [
                'combo_name' => 'Combo Couple (1 Bắp lớn + 2 Nước ngọt)',
                'price' => 95000,
                'description' => '1 bắp lớn vị caramel/phô mai + 2 ly Pepsi lớn.',
            ],
            [
                'combo_name' => 'Combo Family (2 Bắp lớn + 3 Nước ngọt)',
                'price' => 155000,
                'description' => '2 bắp lớn tự chọn vị + 3 ly Pepsi lớn.',
            ],
            [
                'combo_name' => 'Combo Special Kids',
                'price' => 65000,
                'description' => '1 bắp vừa vị ngọt + 1 hộp sữa Milo + 1 đồ chơi lắp ráp.',
            ]
        ];

        return fake()->randomElement($combos);
    }
}
