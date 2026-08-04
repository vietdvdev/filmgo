<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createStaffUser(): User
    {
        $staffRole = Role::create([
            'name' => 'staff',
            'description' => 'Nhân viên rạp',
        ]);

        $cinema = Cinema::create([
            'name' => 'Rạp Test Profile',
            'address' => '123 Đường Test',
            'phone' => '0123456789',
            'city' => 'HCM',
            'status' => 'active',
        ]);

        $staff = User::create([
            'full_name' => 'Staff Test',
            'email' => 'staffprofile@example.com',
            'phone' => '0900000001',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $staff->roles()->attach($staffRole->id);
        $staff->cinemas()->attach($cinema->id);

        return $staff;
    }

    public function test_staff_can_view_profile_page(): void
    {
        $staff = $this->createStaffUser();

        $response = $this->actingAs($staff)->get(route('staff.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Tài Khoản Cá Nhân');
        $response->assertSee('Staff Test');
        $response->assertSee('staffprofile@example.com');
    }

    public function test_staff_can_update_profile_info(): void
    {
        $staff = $this->createStaffUser();

        $response = $this->actingAs($staff)->put(route('staff.profile.update'), [
            'full_name' => 'Staff Mới Cập Nhật',
            'phone'     => '0988776655',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_profile');

        $staff->refresh();
        $this->assertEquals('Staff Mới Cập Nhật', $staff->full_name);
        $this->assertEquals('0988776655', $staff->phone);
    }

    public function test_staff_can_update_password_successfully(): void
    {
        $staff = $this->createStaffUser();

        $response = $this->actingAs($staff)->put(route('staff.profile.password'), [
            'current_password'      => 'password123',
            'new_password'          => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_password');

        $staff->refresh();
        $this->assertTrue(Hash::check('newpassword123', $staff->password));
    }

    public function test_staff_password_update_fails_with_incorrect_current_password(): void
    {
        $staff = $this->createStaffUser();

        $response = $this->actingAs($staff)->put(route('staff.profile.password'), [
            'current_password'      => 'wrongpassword',
            'new_password'          => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $staff->refresh();
        $this->assertTrue(Hash::check('password123', $staff->password));
    }
}
