<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = date('Y');
        $holidays = [
            [
                'name' => 'Tết Dương Lịch',
                'holiday_date' => "$year-01-01",
                'description' => 'Ngày đầu năm mới Dương lịch',
            ],
            [
                'name' => 'Giải Phóng Miền Nam',
                'holiday_date' => "$year-04-30",
                'description' => 'Kỷ niệm Ngày giải phóng miền Nam',
            ],
            [
                'name' => 'Quốc Tế Lao Động',
                'holiday_date' => "$year-05-01",
                'description' => 'Ngày Quốc tế Lao động',
            ],
            [
                'name' => 'Quốc Khánh 2/9',
                'holiday_date' => "$year-09-02",
                'description' => 'Kỷ niệm Ngày Quốc khánh Việt Nam',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(['holiday_date' => $holiday['holiday_date']], $holiday);
        }
    }
}
