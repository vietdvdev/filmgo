<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\ComboItem;
use Illuminate\Database\Seeder;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bapLon  = ComboItem::where('name', 'Bắp lớn')->first();
        $bapNho  = ComboItem::where('name', 'Bắp nhỏ')->first();
        $nuocLon = ComboItem::where('name', 'Nước lớn')->first();
        $nuocNho = ComboItem::where('name', 'Nước nhỏ')->first();
        $snack   = ComboItem::where('name', 'like', '%Snack%')->first();

        $combosData = [
            [
                'combo_name' => 'Combo Single (1 Bắp lớn + 1 Nước lớn)',
                'price' => 75000,
                'image' => 'combos/combo_single.png',
                'description' => '1 Bắp lớn + 1 Nước lớn tự chọn.',
                'status' => 'active',
                'items' => [
                    $bapLon?->id => 1,
                    $nuocLon?->id => 1,
                ],
            ],
            [
                'combo_name' => 'Combo Couple (1 Bắp lớn + 2 Nước lớn)',
                'price' => 95000,
                'image' => 'combos/combo_couple.png',
                'description' => '1 Bắp lớn + 2 Nước lớn tự chọn.',
                'status' => 'active',
                'items' => [
                    $bapLon?->id => 1,
                    $nuocLon?->id => 2,
                ],
            ],
            [
                'combo_name' => 'Combo Family (2 Bắp lớn + 3 Nước lớn)',
                'price' => 155000,
                'image' => 'combos/combo_family.png',
                'description' => '2 Bắp lớn + 3 Nước lớn tùy chọn.',
                'status' => 'active',
                'items' => [
                    $bapLon?->id => 2,
                    $nuocLon?->id => 3,
                ],
            ],
            [
                'combo_name' => 'Combo Special Kids (1 Bắp nhỏ + 1 Nước nhỏ)',
                'price' => 65000,
                'image' => 'combos/combo_kids.png',
                'description' => '1 Bắp nhỏ + 1 Nước nhỏ cho trẻ em.',
                'status' => 'active',
                'items' => [
                    $bapNho?->id => 1,
                    $nuocNho?->id => 1,
                ],
            ]
        ];

        foreach ($combosData as $data) {
            $items = array_filter($data['items'] ?? [], fn($key) => !is_null($key), ARRAY_FILTER_USE_KEY);
            unset($data['items']);

            $combo = Combo::firstOrCreate(['combo_name' => $data['combo_name']], $data);

            if (!empty($items)) {
                $syncData = [];
                foreach ($items as $itemId => $qty) {
                    $syncData[$itemId] = ['quantity' => $qty];
                }
                $combo->items()->sync($syncData);
            }
        }
    }
}
