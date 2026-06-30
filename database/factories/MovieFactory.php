<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Bố Già', 'Đất Rừng Phương Nam', 'Mai', 'Lật Mặt 7: Một Điều Ước',
            'Nhà Bà Nữ', 'Em và Trịnh', 'Mắt Biếc', 'Tiệc Trăng Máu', 'Chị Mười Ba',
            'Gái Già Lắm Chiêu', 'Hai Phượng', 'Song Song', 'Thiên Thần Hộ Mệnh',
            'Đào, Phở và Piano', 'Tháng Năm Rực Rỡ', 'Hồn Papa Da Con Gái'
        ];

        $title = fake()->randomElement($titles);
        $slug = Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999);

        return [
            'title' => $title,
            'slug' => $slug,
            'poster' => 'movies/poster_' . fake()->numberBetween(1, 10) . '.jpg',
            'trailer_url' => 'https://www.youtube.com/embed/' . Str::random(11),
            'duration' => fake()->randomElement([90, 105, 120, 135, 150]),
            'release_date' => fake()->dateTimeBetween('-2 months', '+1 month')->format('Y-m-d'),
            'director' => fake()->name(),
            'country' => fake()->randomElement(['Việt Nam', 'Mỹ', 'Hàn Quốc', 'Nhật Bản', 'Trung Quốc']),
            'age_limit' => fake()->randomElement(['P', 'K', 'T13', 'T16', 'T18']),
            'description' => fake()->paragraph(3),
            'status' => fake()->randomElement(['upcoming', 'showing', 'stopped']),
        ];
    }
}
