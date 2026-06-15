<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\User;
use App\Models\UserCinema;
use Illuminate\Database\Seeder;

class UserCinemaSeeder extends Seeder
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

        // Lấy tất cả quản lý và nhân viên
        $staffAndManagers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['manager', 'staff']);
        })->get();

        foreach ($staffAndManagers as $user) {
            // Phân bổ ngẫu nhiên vào một rạp chiếu
            $cinema = $cinemas->random();

            UserCinema::firstOrCreate([
                'user_id' => $user->id,
                'cinema_id' => $cinema->id,
            ], [
                'created_at' => now(),
            ]);
        }
    }
}
