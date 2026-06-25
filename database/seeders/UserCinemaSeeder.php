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

        // Đảm bảo manager mặc định luôn được phân công vào rạp đầu tiên
        $defaultManager = User::where('email', 'manager@filmgo.vn')->first();
        if ($defaultManager) {
            UserCinema::firstOrCreate(
                ['user_id' => $defaultManager->id, 'cinema_id' => $cinemas->first()->id],
                ['created_at' => now()]
            );
        }

        // Lấy tất cả quản lý và nhân viên còn lại
        $staffAndManagers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['manager', 'staff']);
        })->get();

        foreach ($staffAndManagers as $user) {
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
