<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\ConflictPayment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminDashboardApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Lấy hoặc tạo admin user để authenticate
        $this->adminUser = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();

        if (!$this->adminUser) {
            $this->adminUser = User::factory()->create();
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $this->adminUser->roles()->attach($adminRole->id);
        }
    }

    /**
     * Test API KPIs với bộ lọc ngày
     */
    public function test_kpi_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/dashboard/kpis?start_date=2026-07-16&end_date=2026-07-17');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'revenue' => [
                    'today' => ['total', 'ticket', 'combo'],
                    'yesterday' => ['total', 'ticket', 'combo'],
                    'growth' => ['total_pct', 'ticket_pct', 'combo_pct']
                ],
                'tickets' => ['today', 'yesterday', 'growth_pct'],
                'occupancy' => ['today_rate', 'yesterday_rate', 'growth_points', 'today_booked_seats', 'today_total_seats'],
                'payment_methods' => ['online_pct', 'counter_pct', 'online_revenue', 'counter_revenue']
            ]);
    }

    /**
     * Test API charts revenue với bộ lọc ngày
     */
    public function test_charts_revenue_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/dashboard/charts/revenue?start_date=2026-07-16&end_date=2026-07-17');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'labels',
                'ticket_revenue',
                'combo_revenue'
            ]);
    }

    /**
     * Test API charts top movies với bộ lọc ngày
     */
    public function test_charts_top_movies_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/dashboard/charts/top-movies?start_date=2026-07-16&end_date=2026-07-17');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_tickets_in_period',
                'top_movies' => [
                    '*' => ['title', 'tickets_count', 'percentage']
                ]
            ]);
    }

    /**
     * Test API ops conflicts
     */
    public function test_ops_conflicts_endpoint_returns_json(): void
    {
        // Đảm bảo có ít nhất 1 conflict payment để test
        ConflictPayment::create([
            'booking_code' => 'FG-12345678',
            'transaction_code' => 'TXN998877',
            'amount' => 120000,
            'payment_method' => 'vnpay',
            'reason' => 'Thanh toan muon',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/dashboard/ops/conflicts');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test API ops today showtimes
     */
    public function test_ops_today_showtimes_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/dashboard/ops/today-showtimes');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }
}
