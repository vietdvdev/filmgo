<?php

namespace Database\Seeders;

use App\Models\ComboItem;
use Illuminate\Database\Seeder;

class ComboItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Bắp lớn',
                'type' => 'popcorn',
                'unit' => 'Hộp',
                'price' => 50000,
                'status' => 'active',
            ],
            [
                'name' => 'Bắp nhỏ',
                'type' => 'popcorn',
                'unit' => 'Hộp',
                'price' => 35000,
                'status' => 'active',
            ],
            [
                'name' => 'Nước lớn',
                'type' => 'drink',
                'unit' => 'Ly',
                'price' => 30000,
                'status' => 'active',
            ],
            [
                'name' => 'Nước nhỏ',
                'type' => 'drink',
                'unit' => 'Ly',
                'price' => 20000,
                'status' => 'active',
            ],
            [
                'name' => 'Snack / Snack khoai tây',
                'type' => 'snack',
                'unit' => 'Gói',
                'price' => 25000,
                'status' => 'active',
            ],
        ];

        foreach ($items as $item) {
            ComboItem::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
