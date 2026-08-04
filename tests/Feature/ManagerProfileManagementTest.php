<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManagerProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createManagerUser(): User
    {
        $managerRole = Role::create([
            'name' => 'manager',
            'description' => 'Quản lý rạp',
        ]);

        $cinema = Cinema::create([
            'name' => 'Rạp Test Manager Profile',
            'address' => '456 Đường Test',
            'phone' => '0987654321',
            'city' => 'Hà Nội',
            'status' => 'active',
        ]);

        $manager = User::create([
            'full_name' => 'Manager Test Profile',
            'email' => 'managerprofile@example.com',
            'phone' => '0911111111',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $manager->roles()->attach($managerRole->id);
        $manager->cinemas()->attach($cinema->id);

        return $manager;
    }

    public function test_manager_can_view_profile_page(): void
    {
        $manager = $this->createManagerUser();

        $response = $this->actingAs($manager)->get(route('manager.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Tài Khoản Cá Nhân');
        $response->assertSee('Manager Test Profile');
        $response->assertSee('managerprofile@example.com');
    }

    public function test_manager_can_update_profile_info(): void
    {
        $manager = $this->createManagerUser();

        $response = $this->actingAs($manager)->put(route('manager.profile.update'), [
            'full_name' => 'Manager Mới Cập Nhật',
            'phone'     => '0933333333',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_profile');

        $manager->refresh();
        $this->assertEquals('Manager Mới Cập Nhật', $manager->full_name);
        $this->assertEquals('0933333333', $manager->phone);
    }

    public function test_manager_can_update_password_successfully(): void
    {
        $manager = $this->createManagerUser();

        $response = $this->actingAs($manager)->put(route('manager.profile.password'), [
            'current_password'          => 'password123',
            'new_password'              => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_password');

        $manager->refresh();
        $this->assertTrue(Hash::check('newpassword123', $manager->password));
    }

    public function test_manager_password_update_fails_with_incorrect_current_password(): void
    {
        $manager = $this->createManagerUser();

        $response = $this->actingAs($manager)->put(route('manager.profile.password'), [
            'current_password'          => 'wrongpassword',
            'new_password'              => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $manager->refresh();
        $this->assertTrue(Hash::check('password123', $manager->password));
    }
}
