<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            ['name' => 'Hành động', 'description' => 'Phim có nhiều pha hành động, kịch tính, rượt đuổi, võ thuật.'],
            ['name' => 'Kinh dị', 'description' => 'Phim mang yếu tố giật gân, đáng sợ, tâm linh hoặc ma quái.'],
            ['name' => 'Tình cảm', 'description' => 'Phim tập trung vào các câu chuyện tình yêu lãng mạn.'],
            ['name' => 'Hài hước', 'description' => 'Phim mang lại tiếng cười giải trí và các tình huống dí dỏ.'],
            ['name' => 'Viễn tưởng', 'description' => 'Phim khoa học viễn tưởng, công nghệ tương lai, vũ trụ.'],
            ['name' => 'Hoạt hình', 'description' => 'Phim dành cho thiếu nhi và gia đình sử dụng đồ họa vẽ tay hoặc 3D.'],
            ['name' => 'Tâm lý', 'description' => 'Phim đi sâu vào khai thác diễn biến tâm lý nhân vật phức tạp.'],
            ['name' => 'Phiêu lưu', 'description' => 'Phim kể về các chuyến hành trình khám phá đầy mạo hiểm.'],
        ];

        foreach ($genres as $genre) {
            Genre::firstOrCreate(['name' => $genre['name']], $genre);
        }
    }
}
