<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cinemas = Cinema::all();
        if ($cinemas->isEmpty()) {
            return;
        }

        $normalType = SeatType::where('name', 'Thường')->first();
        $vipType = SeatType::where('name', 'VIP')->first();
        $sweetboxType = SeatType::where('name', 'Sweetbox')->first();

        foreach ($cinemas as $cinema) {
            // Mỗi rạp có từ 2 đến 4 phòng chiếu
            $roomCount = rand(2, 4);

            for ($i = 1; $i <= $roomCount; $i++) {
                $capacity = rand(60, 90); // Quy mô vừa phải để seed nhanh và mượt mà
                
                $room = Room::create([
                    'cinema_id' => $cinema->id,
                    'room_name' => "Phòng chiếu $i",
                    'capacity' => $capacity,
                    'room_type' => fake()->randomElement(['2D', '3D', 'IMAX', '4DX']),
                    'status' => 'active',
                ]);

                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];
                $seatsPerRow = 10;
                $seatsToInsert = [];
                $totalGenerated = 0;

                foreach ($rows as $rowLetter) {
                    if ($totalGenerated >= $capacity) break;

                    for ($num = 1; $num <= $seatsPerRow; $num++) {
                        if ($totalGenerated >= $capacity) break;

                        if (in_array($rowLetter, ['A', 'B', 'C', 'D'])) {
                            $typeId = $normalType->id;
                        } elseif (in_array($rowLetter, ['E', 'F', 'G', 'H'])) {
                            $typeId = $vipType->id;
                        } else {
                            $typeId = $sweetboxType->id;
                        }

                        $seatsToInsert[] = [
                            'room_id'      => $room->id,
                            'seat_type_id' => $typeId,
                            'seat_row'     => $rowLetter,
                            'seat_number'  => $num,
                            'status'       => 'active',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];

                        $totalGenerated++;
                    }
                }

                Seat::insert($seatsToInsert);
            }
        }
    }
}
