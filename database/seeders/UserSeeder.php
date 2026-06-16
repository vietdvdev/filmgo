<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'full_name' => 'Thùy Trang Admin',
                'phone' => '0987654321',
                'password' => Hash::make('123456'),
                'status' => 'active',
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }

        // Tạo thêm 15 người dùng (khách hàng) để đảm bảo có đủ dữ liệu cho các tính năng khác (đánh giá, vé đặt...)
        User::factory()->count(15)->create()->each(function ($user) {
            $customerRole = Role::where('name', 'customer')->first();
            if ($customerRole) {
                $user->roles()->attach($customerRole->id);
            }
        });
    }
}
