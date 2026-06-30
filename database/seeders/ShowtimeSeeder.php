<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Database\Seeder;

class ShowtimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = Movie::where('status', 'showing')->get();
        if ($movies->isEmpty()) {
            $movies = Movie::all();
        }
        $rooms = Room::with('seats')->where('status', 'active')->get();

        if ($movies->isEmpty() || $rooms->isEmpty()) {
            return;
        }

        $dates = [
            date('Y-m-d'), 
            date('Y-m-d', strtotime('+1 day')), 
            date('Y-m-d', strtotime('+2 days'))
        ];

        $timeSlots = [
            ['start' => '10:00:00', 'end' => '12:00:00'],
            ['start' => '13:30:00', 'end' => '15:30:00'],
            ['start' => '17:00:00', 'end' => '19:00:00'],
            ['start' => '20:00:00', 'end' => '22:00:00'],
        ];

        // Giới hạn chỉ seed cho 3 rạp đầu tiên để tránh tràn dữ liệu
        $limitedCinemaIds = Cinema::orderBy('id')->limit(3)->pluck('id');

        foreach ($rooms as $room) {
            if (!$limitedCinemaIds->contains($room->cinema_id)) {
                continue;
            }

            foreach ($dates as $date) {
                // Chọn ngẫu nhiên 2 khung giờ chiếu mỗi ngày cho phòng này
                $slots = fake()->randomElements($timeSlots, 2);

                foreach ($slots as $slot) {
                    $movie = $movies->random();

                    $showtime = Showtime::create([
                        'movie_id'   => $movie->id,
                        'room_id'    => $room->id,
                        'show_date'  => $date,
                        'start_time' => $slot['start'],
                        'end_time'   => $slot['end'],
                        'base_price' => fake()->randomElement([60000, 75000, 90000]),
                        'status'     => 'upcoming',
                    ]);

                    // Batch insert toàn bộ ghế trong 1 query thay vì từng INSERT riêng
                    $seatsData = $room->seats->map(fn($seat) => [
                        'showtime_id' => $showtime->id,
                        'seat_id'     => $seat->id,
                        'user_id'     => null,
                        'status'      => 'available',
                        'locked_at'   => null,
                        'expires_at'  => null,
                    ])->toArray();

                    ShowtimeSeat::insert($seatsData);
                }
            }
        }
    }
}
