<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();

        // 1. Tạo Admin
        $admin = User::create([
            'full_name' => 'Nguyễn Văn Admin',
            'email' => 'admin@filmgo.vn',
            'phone' => '0987654321',
            'password' => Hash::make('admin123'),
            'avatar' => 'avatars/admin.png',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($adminRole);

        // 2. Tạo Managers
        $managers = [
            [
                'full_name' => 'Trần Thị Quản Lý 1',
                'email' => 'manager1@filmgo.vn',
                'phone' => '0912345678',
                'password' => Hash::make('manager123'),
                'avatar' => 'avatars/manager1.png',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'full_name' => 'Phạm Văn Quản Lý 2',
                'email' => 'manager2@filmgo.vn',
                'phone' => '0912345679',
                'password' => Hash::make('manager123'),
                'avatar' => 'avatars/manager2.png',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        ];
        foreach ($managers as $mgrData) {
            $mgr = User::create($mgrData);
            $mgr->roles()->attach($managerRole);
        }

        // 3. Tạo Staffs
        $staffs = [
            [
                'full_name' => 'Lê Văn Nhân Viên 1',
                'email' => 'staff1@filmgo.vn',
                'phone' => '0922345678',
                'password' => Hash::make('staff123'),
                'avatar' => 'avatars/staff1.png',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'full_name' => 'Hoàng Thị Nhân Viên 2',
                'email' => 'staff2@filmgo.vn',
                'phone' => '0922345679',
                'password' => Hash::make('staff123'),
                'avatar' => 'avatars/staff2.png',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        ];
        foreach ($staffs as $staffData) {
            $stf = User::create($staffData);
            $stf->roles()->attach($staffRole);
        }

        // 4. Tạo Customers ngẫu nhiên bằng Factory
        User::factory()->count(20)->create()->each(function ($user) use ($customerRole) {
            $user->roles()->attach($customerRole);
        });
    }
}
