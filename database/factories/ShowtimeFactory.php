<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Showtime>
 */
class ShowtimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->randomElement(['09:00:00', '11:30:00', '14:00:00', '16:30:00', '19:00:00', '21:30:00', '23:45:00']);
        $startSeconds = strtotime($startTime);
        $endSeconds = $startSeconds + (120 * 60); // mặc định cộng thêm 2 tiếng
        $endTime = date('H:i:s', $endSeconds);

        return [
            'movie_id' => Movie::factory(),
            'room_id' => Room::factory(),
            'show_date' => fake()->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'base_price' => fake()->randomElement([60000, 75000, 90000, 100000]),
            'status' => 'upcoming',
        ];
    }
}
