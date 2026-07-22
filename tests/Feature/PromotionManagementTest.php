<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo vai trò admin và user admin
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Quản trị viên']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole->id);
    }

    public function test_admin_can_view_promotion_list(): void
    {
        Promotion::create([
            'code'              => 'TESTPROMO',
            'apply_to'          => 'all',
            'discount_type'     => 'percent',
            'discount_value'    => 20,
            'min_order_amount'  => 50000,
            'max_uses_per_user' => 1,
            'start_date'        => now(),
            'end_date'          => now()->addDays(7),
            'status'            => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.promotions.index'));

        $response->assertStatus(200);
        $response->assertSee('TESTPROMO');
    }

    public function test_admin_can_create_a_percent_promotion(): void
    {
        $payload = [
            'code'                => 'summer2026',
            'apply_to'            => 'all',
            'discount_type'       => 'percent',
            'discount_value'      => 15,
            'max_discount_amount' => 50000,
            'min_order_amount'    => 100000,
            'max_uses_per_user'   => 2,
            'start_date'          => now()->format('Y-m-d\TH:i'),
            'end_date'            => now()->addDays(10)->format('Y-m-d\TH:i'),
            'usage_limit'         => 100,
            'status'              => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.promotions.store'), $payload);

        $response->assertRedirect(route('admin.promotions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('promotions', [
            'code'                => 'SUMMER2026',
            'apply_to'            => 'all',
            'discount_type'       => 'percent',
            'discount_value'      => 15,
            'max_discount_amount' => 50000,
            'usage_limit'         => 100,
        ]);
    }

    public function test_admin_can_create_a_fixed_amount_promotion(): void
    {
        $payload = [
            'code'              => 'FIXED30K',
            'apply_to'          => 'ticket_only',
            'discount_type'     => 'fixed',
            'discount_value'    => 30000,
            'min_order_amount'  => 50000,
            'max_uses_per_user' => 1,
            'start_date'        => now()->format('Y-m-d\TH:i'),
            'end_date'          => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status'            => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.promotions.store'), $payload);

        $response->assertRedirect(route('admin.promotions.index'));
        $this->assertDatabaseHas('promotions', [
            'code'                => 'FIXED30K',
            'apply_to'            => 'ticket_only',
            'discount_type'       => 'fixed',
            'discount_value'      => 30000,
            'max_discount_amount' => null,
        ]);
    }

    public function test_admin_cannot_create_promotion_with_percent_exceeding_100(): void
    {
        $payload = [
            'code'              => 'OVER100',
            'apply_to'          => 'all',
            'discount_type'     => 'percent',
            'discount_value'    => 150, // Invalid percent
            'min_order_amount'  => 0,
            'max_uses_per_user' => 1,
            'start_date'        => now()->format('Y-m-d\TH:i'),
            'end_date'          => now()->addDays(3)->format('Y-m-d\TH:i'),
            'status'            => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.promotions.store'), $payload);

        $response->assertSessionHasErrors(['discount_value']);
    }

    public function test_admin_can_update_promotion(): void
    {
        $promotion = Promotion::create([
            'code'              => 'OLDCODE',
            'apply_to'          => 'all',
            'discount_type'     => 'fixed',
            'discount_value'    => 10000,
            'min_order_amount'  => 0,
            'max_uses_per_user' => 1,
            'start_date'        => now(),
            'end_date'          => now()->addDays(2),
            'status'            => 'active',
        ]);

        $payload = [
            'code'              => 'NEWCODE',
            'apply_to'          => 'combo_only',
            'discount_type'     => 'fixed',
            'discount_value'    => 20000,
            'min_order_amount'  => 30000,
            'max_uses_per_user' => 2,
            'start_date'        => now()->format('Y-m-d\TH:i'),
            'end_date'          => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status'            => 'active',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.promotions.update', $promotion->id), $payload);

        $response->assertRedirect(route('admin.promotions.index'));
        $this->assertDatabaseHas('promotions', [
            'id'             => $promotion->id,
            'code'           => 'NEWCODE',
            'apply_to'       => 'combo_only',
            'discount_value' => 20000,
        ]);
    }

    public function test_admin_can_delete_promotion(): void
    {
        $promotion = Promotion::create([
            'code'              => 'TODELETE',
            'apply_to'          => 'all',
            'discount_type'     => 'fixed',
            'discount_value'    => 10000,
            'min_order_amount'  => 0,
            'max_uses_per_user' => 1,
            'start_date'        => now(),
            'end_date'          => now()->addDays(2),
            'status'            => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.promotions.destroy', $promotion->id));

        $response->assertRedirect(route('admin.promotions.index'));
        $this->assertSoftDeleted('promotions', ['id' => $promotion->id]);
    }
}
