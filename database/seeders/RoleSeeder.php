<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Quản trị viên toàn quyền hệ thống',
            ],
            [
                'name' => 'manager',
                'description' => 'Quản lý chi nhánh rạp phim',
            ],
            [
                'name' => 'staff',
                'description' => 'Nhân viên bán vé và vận hành tại rạp',
            ],
            [
                'name' => 'customer',
                'description' => 'Khách hàng mua vé xem phim',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
