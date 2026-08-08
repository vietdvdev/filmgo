<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Combo;
use App\Models\Role;
use App\Models\User;
use App\Services\ComboOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffComboBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_combo_order_creates_ticket_and_is_visible_to_staff(): void
    {
        $staffRole = Role::create([
            'name' => 'staff',
            'description' => 'Nhân viên rạp',
        ]);

        $cinema = Cinema::create([
            'name' => 'Rạp Test',
            'address' => 'Địa chỉ test',
            'phone' => '0123456789',
            'city' => 'HCM',
            'status' => 'active',
        ]);

        $staff = User::create([
            'full_name' => 'Staff Test',
            'email' => 'staff@example.com',
            'phone' => '0900000001',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $staff->roles()->attach($staffRole->id);
        $staff->cinemas()->attach($cinema->id);

        $customer = User::factory()->create();
        $combo = Combo::factory()->create(['status' => 'active']);

        $service = app(ComboOrderService::class);
        $booking = $service->createCustomerComboOrder($customer->id, [$combo->id => 1], []);
        $booking->update(['payment_status' => 'paid', 'booking_status' => 'confirmed']);

        $bookingDetail = $booking->refresh()->bookingDetails()->first();
        $this->assertNotNull($bookingDetail);
        $this->assertNotNull($bookingDetail->ticket);
        $this->assertNotEmpty($bookingDetail->ticket->qr_code);

        $response = $this->actingAs($staff)->get(route('staff.combo-bookings.index'));
        $response->assertOk();
        $response->assertSee($booking->booking_code);
    }

    public function test_staff_can_view_combo_booking_management_for_assigned_cinema(): void
    {
        $staffRole = Role::create([
            'name' => 'staff',
            'description' => 'Nhân viên rạp',
        ]);

        $cinema = Cinema::create([
            'name' => 'Rạp Test',
            'address' => 'Địa chỉ test',
            'phone' => '0123456789',
            'city' => 'HCM',
            'status' => 'active',
        ]);

        $otherCinema = Cinema::create([
            'name' => 'Rạp Khác',
            'address' => 'Địa chỉ khác',
            'phone' => '0987654321',
            'city' => 'HCM',
            'status' => 'active',
        ]);

        $staff = User::create([
            'full_name' => 'Staff Test',
            'email' => 'staff@example.com',
            'phone' => '0900000001',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $staff->roles()->attach($staffRole->id);
        $staff->cinemas()->attach($cinema->id);

        $otherStaff = User::create([
            'full_name' => 'Staff Khác',
            'email' => 'staff2@example.com',
            'phone' => '0900000002',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $otherStaff->roles()->attach($staffRole->id);
        $otherStaff->cinemas()->attach($otherCinema->id);

        $visibleBooking = Booking::create([
            'user_id' => $staff->id,
            'staff_id' => $staff->id,
            'showtime_id' => null,
            'booking_code' => 'FG-COMBO-001',
            'subtotal' => 90000,
            'total_amount' => 90000,
            'discount_amount' => 0,
            'final_total' => 90000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'channel' => 'counter',
            'booking_type' => 'combo_only',
            'expired_at' => now()->addMinutes(30),
        ]);

        $hiddenBooking = Booking::create([
            'user_id' => $otherStaff->id,
            'staff_id' => $otherStaff->id,
            'showtime_id' => null,
            'booking_code' => 'FG-COMBO-999',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'discount_amount' => 0,
            'final_total' => 50000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'channel' => 'counter',
            'booking_type' => 'combo_only',
            'expired_at' => now()->addMinutes(30),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.combo-bookings.index'));

        $response->assertOk();
        $response->assertSee($visibleBooking->booking_code);
        $response->assertDontSee($hiddenBooking->booking_code);
    }
}
