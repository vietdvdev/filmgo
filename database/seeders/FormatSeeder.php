<?php

namespace Database\Seeders;

use App\Models\Format;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class FormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formats = [
            ['name' => '2D', 'surcharge_price' => 0],
            ['name' => '3D', 'surcharge_price' => 30000],
            ['name' => 'IMAX', 'surcharge_price' => 60000],
            ['name' => '4DX', 'surcharge_price' => 80000],
        ];

        foreach ($formats as $item) {
            Format::updateOrCreate(
                ['name' => $item['name']],
                ['surcharge_price' => $item['surcharge_price']]
            );
        }

        $allFormats = Format::all();
        $format2D = $allFormats->where('name', '2D')->first();
        $format3D = $allFormats->where('name', '3D')->first();
        $formatImax = $allFormats->where('name', 'IMAX')->first();

        // Gán định dạng cho tất cả các phim
        Movie::all()->each(function ($movie) use ($format2D, $format3D, $formatImax) {
            // Mọi phim đều có định dạng 2D
            $syncFormats = [$format2D->id];

            // 50% phim ngẫu nhiên hỗ trợ thêm 3D / IMAX
            if ($movie->id % 2 === 0 && $format3D) {
                $syncFormats[] = $format3D->id;
            }
            if ($movie->id % 3 === 0 && $formatImax) {
                $syncFormats[] = $formatImax->id;
            }

            $movie->formats()->sync($syncFormats);
        });
    }
}
