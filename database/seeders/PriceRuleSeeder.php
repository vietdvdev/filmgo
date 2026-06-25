<?php

namespace Database\Seeders;

use App\Models\PriceRule;
use Illuminate\Database\Seeder;

class PriceRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Cuối Tuần Giờ Vàng (Thứ Bảy & Chủ Nhật)',
                'day_of_week' => 6, // Áp dụng cho Thứ 7 (sẽ viết code kiểm tra cả Chủ Nhật 0 ở app layer)
                'start_time' => '18:00:00',
                'end_time' => '22:30:00',
                'adjustment_amount' => 20000,
                'is_active' => 1,
            ],
            [
                'name' => 'Suất Chiếu Đêm Khuya',
                'day_of_week' => null, // Áp dụng mọi ngày trong tuần
                'start_time' => '22:30:00',
                'end_time' => '23:59:59',
                'adjustment_amount' => -10000,
                'is_active' => 1,
            ],
            [
                'name' => 'Suất Chiếu Sớm Đầu Ngày',
                'day_of_week' => null, // Áp dụng mọi ngày trong tuần
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
                'adjustment_amount' => -15000,
                'is_active' => 1,
            ],
        ];

        // Seed 6 quy tắc bổ sung đại diện cho các thứ từ thứ 2 đến chủ nhật
        for ($i = 0; $i <= 6; $i++) {
            PriceRule::create([
                'name' => 'Quy tắc Giá Ngày Thường - Thứ ' . ($i === 0 ? 'Chủ Nhật' : ($i + 1)),
                'day_of_week' => $i,
                'start_time' => '12:00:00',
                'end_time' => '18:00:00',
                'adjustment_amount' => ($i === 0 || $i === 6) ? 10000 : 0, // Cuối tuần tăng nhẹ
                'is_active' => 1,
            ]);
        }

        foreach ($rules as $rule) {
            PriceRule::create($rule);
        }
    }
}
