<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'code' => 'SALE10',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_order_amount' => 50000,
                'max_uses_per_user' => 1,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'quantity' => 100,
                'status' => 'active',
            ],
            [
                'code' => 'SALE20',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'min_order_amount' => 100000,
                'max_uses_per_user' => 1,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'quantity' => 50,
                'status' => 'active',
            ],
            [
                'code' => 'FILMGO50',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 150000,
                'max_uses_per_user' => 1,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'quantity' => 30,
                'status' => 'active',
            ],
            [
                'code' => 'STUDENT',
                'discount_type' => 'fixed',
                'discount_value' => 15000,
                'min_order_amount' => 0,
                'max_uses_per_user' => 10,
                'start_date' => now()->subDay(),
                'end_date' => now()->addYear(),
                'quantity' => null,
                'status' => 'active',
            ]
        ];

        foreach ($promotions as $promo) {
            Promotion::firstOrCreate(['code' => $promo['code']], $promo);
        }
    }
}
