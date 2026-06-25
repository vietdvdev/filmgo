<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $movies = Movie::all();

        if ($users->isEmpty() || $movies->isEmpty()) {
            return;
        }

        $comments = [
            'Phim rất hay, nội dung ý nghĩa và đáng xem!',
            'Kỹ xảo điện ảnh đỉnh cao, diễn viên diễn xuất rất tốt.',
            'Kịch bản phim hơi dài dòng nhưng kết thúc rất xúc động.',
            'Nhạc phim hay xuất sắc, cảnh quay đẹp mắt, khuyên mọi người nên đi xem.',
            'Nội dung bình thường, không quá đặc sắc nhưng diễn xuất của nam chính gánh cả bộ phim.',
            'Rất thích thông điệp bộ phim truyền tải. 10 điểm!'
        ];

        // Mỗi bộ phim có từ 2 đến 5 lượt đánh giá từ các người dùng ngẫu nhiên
        foreach ($movies as $movie) {
            $reviewCount = rand(1, min(5, $users->count()));
            $randomUsers = $users->random($reviewCount);
            // random(1) trả về Model thay vì Collection, wrap lại cho an toàn
            if (!($randomUsers instanceof \Illuminate\Support\Collection)) {
                $randomUsers = collect([$randomUsers]);
            }

            foreach ($randomUsers as $user) {
                // Sử dụng firstOrCreate để tránh trùng lặp UNIQUE (user_id, movie_id)
                Review::firstOrCreate([
                    'user_id' => $user->id,
                    'movie_id' => $movie->id,
                ], [
                    'rating' => rand(3, 5),
                    'comment' => fake()->randomElement($comments),
                    'status' => 'approved',
                    'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
                ]);
            }
        }
    }
}
