<?php

namespace Database\Seeders;

use App\Models\Combo;
use Illuminate\Database\Seeder;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $combos = [
            [
                'combo_name' => 'Combo Single (1 Bắp lớn + 1 Nước ngọt)',
                'price' => 75000,
                'image' => 'combos/combo_single.png',
                'description' => '1 bắp lớn vị caramel hoặc phô mai + 1 ly nước ngọt lớn (Pepsi/Sprite/Fanta).',
                'status' => 'active',
            ],
            [
                'combo_name' => 'Combo Couple (1 Bắp lớn + 2 Nước ngọt)',
                'price' => 95000,
                'image' => 'combos/combo_couple.png',
                'description' => '1 bắp lớn vị caramel hoặc phô mai + 2 ly nước ngọt lớn tự chọn.',
                'status' => 'active',
            ],
            [
                'combo_name' => 'Combo Family (2 Bắp lớn + 3 Nước ngọt)',
                'price' => 155000,
                'image' => 'combos/combo_family.png',
                'description' => '2 bắp lớn tự chọn vị ngọt/mặn + 3 ly nước ngọt lớn tùy chọn.',
                'status' => 'active',
            ],
            [
                'combo_name' => 'Combo Special Kids (1 Bắp nhỏ + 1 Sữa hộp)',
                'price' => 65000,
                'image' => 'combos/combo_kids.png',
                'description' => '1 bắp vừa vị ngọt + 1 hộp sữa Milo + 1 đồ chơi lắp ráp nhân vật hoạt hình.',
                'status' => 'active',
            ]
        ];

        foreach ($combos as $combo) {
            Combo::firstOrCreate(['combo_name' => $combo['combo_name']], $combo);
        }
    }
}
