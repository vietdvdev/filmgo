<?php

namespace Database\Seeders;

use App\Models\SeatType;
use Illuminate\Database\Seeder;

class SeatTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Thường',
                'surcharge_price' => 0,
            ],
            [
                'name' => 'VIP',
                'surcharge_price' => 20000,
            ],
            [
                'name' => 'Sweetbox',
                'surcharge_price' => 40000,
            ],
        ];

        foreach ($types as $type) {
            SeatType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
