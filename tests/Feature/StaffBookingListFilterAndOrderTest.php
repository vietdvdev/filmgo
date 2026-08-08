<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Format;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\User;
use App\Services\StaffBookingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StaffBookingListFilterAndOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected User $staff;
    protected Cinema $cinema;
    protected Room $room;
    protected Showtime $showtime;

    protected function setUp(): void
    {
        parent::setUp();

        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['description' => 'Nhân viên rạp']);
        $this->cinema = Cinema::create([
            'name' => 'Rạp Test Staff Filter',
            'address' => '123 Đường Test',
            'phone' => '0901112223',
            'city' => 'Hà Nội',
            'status' => 'active',
        ]);

        $this->staff = User::factory()->create();
        $this->staff->roles()->attach($staffRole->id);
        $this->staff->cinemas()->attach($this->cinema->id);

        $movie = Movie::factory()->create(['duration' => 120]);
        $format = Format::firstOrCreate(['name' => '2D']);
        $this->room = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Phòng 01',
            'room_type' => '2D',
            'capacity' => 100,
            'status' => 'active',
        ]);

        $this->showtime = Showtime::create([
            'movie_id' => $movie->id,
            'format_id' => $format->id,
            'room_id' => $this->room->id,
            'show_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'base_price' => 80000,
            'status' => 'active',
        ]);
    }

    /**
     * Test getDailyBookingsByCinema filters ONLY paid/confirmed bookings and orders newest first.
     */
    public function test_staff_ticket_booking_list_shows_only_paid_and_newest_first(): void
    {
        $customer = User::factory()->create();

        // 1. Tạo đơn 1: Đã thanh toán (tạo lúc cũ hơn)
        $paidBooking1 = Booking::create([
            'user_id' => $customer->id,
            'showtime_id' => $this->showtime->id,
            'booking_code' => 'TICKET-PAID-001',
            'subtotal' => 100000,
            'total_amount' => 100000,
            'discount_amount' => 0,
            'final_total' => 100000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'channel' => 'counter',
            'booking_type' => 'ticket',
            'expired_at' => now()->addMinutes(30),
            'created_at' => now()->subMinutes(10),
        ]);

        // 2. Tạo đơn 2: Đã thanh toán (tạo mới hơn)
        $paidBooking2 = Booking::create([
            'user_id' => $customer->id,
            'showtime_id' => $this->showtime->id,
            'booking_code' => 'TICKET-PAID-002',
            'subtotal' => 150000,
            'total_amount' => 150000,
            'discount_amount' => 0,
            'final_total' => 150000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'channel' => 'counter',
            'booking_type' => 'ticket',
            'expired_at' => now()->addMinutes(30),
            'created_at' => now()->subMinutes(2),
        ]);

        // 3. Tạo đơn 3: Chưa thanh toán (pending) -> PHẢI BỊ LỌC BỎ
        $pendingBooking = Booking::create([
            'user_id' => $customer->id,
            'showtime_id' => $this->showtime->id,
            'booking_code' => 'TICKET-PENDING-003',
            'subtotal' => 80000,
            'total_amount' => 80000,
            'discount_amount' => 0,
            'final_total' => 80000,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'channel' => 'online',
            'booking_type' => 'ticket',
            'expired_at' => now()->addMinutes(15),
            'created_at' => now()->subMinute(),
        ]);

        $service = app(StaffBookingService::class);
        $result = $service->getDailyBookingsByCinema($this->cinema->id, now()->toDateString());

        // Kiểm tra danh sách chỉ chứa 2 đơn đã thanh toán
        $codes = collect($result->items())->pluck('booking_code')->toArray();
        
        $this->assertContains('TICKET-PAID-001', $codes);
        $this->assertContains('TICKET-PAID-002', $codes);
        $this->assertNotContains('TICKET-PENDING-003', $codes);

        // Kiểm tra thứ tự: Đơn mới tạo sau (TICKET-PAID-002) đứng TRƯỚC đơn tạo trước (TICKET-PAID-001)
        $paid1Index = array_search('TICKET-PAID-001', $codes);
        $paid2Index = array_search('TICKET-PAID-002', $codes);
        $this->assertLessThan($paid1Index, $paid2Index);
    }

    /**
     * Test getDailyComboBookingsByCinema filters ONLY paid/confirmed combo bookings and orders newest first.
     */
    public function test_staff_combo_booking_list_shows_only_paid_and_newest_first(): void
    {
        $customer = User::factory()->create();

        $paidCombo1 = Booking::create([
            'user_id' => $customer->id,
            'staff_id' => $this->staff->id,
            'showtime_id' => null,
            'cinema_id' => $this->cinema->id,
            'booking_code' => 'COMBO-PAID-001',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'discount_amount' => 0,
            'final_total' => 50000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'channel' => 'counter',
            'booking_type' => 'combo_only',
            'expired_at' => now()->addMinutes(30),
            'created_at' => now()->subMinutes(15),
        ]);

        $paidCombo2 = Booking::create([
            'user_id' => $customer->id,
            'staff_id' => $this->staff->id,
            'showtime_id' => null,
            'cinema_id' => $this->cinema->id,
            'booking_code' => 'COMBO-PAID-002',
            'subtotal' => 120000,
            'total_amount' => 120000,
            'discount_amount' => 0,
            'final_total' => 120000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'channel' => 'counter',
            'booking_type' => 'combo_only',
            'expired_at' => now()->addMinutes(30),
            'created_at' => now()->subMinutes(1),
        ]);

        $pendingCombo = Booking::create([
            'user_id' => $customer->id,
            'staff_id' => $this->staff->id,
            'showtime_id' => null,
            'cinema_id' => $this->cinema->id,
            'booking_code' => 'COMBO-PENDING-003',
            'subtotal' => 70000,
            'total_amount' => 70000,
            'discount_amount' => 0,
            'final_total' => 70000,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'channel' => 'online',
            'booking_type' => 'combo_only',
            'expired_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        $service = app(StaffBookingService::class);
        $result = $service->getDailyComboBookingsByCinema($this->cinema->id, now()->toDateString());

        $codes = collect($result->items())->pluck('booking_code')->toArray();

        $this->assertContains('COMBO-PAID-001', $codes);
        $this->assertContains('COMBO-PAID-002', $codes);
        $this->assertNotContains('COMBO-PENDING-003', $codes);

        $combo1Index = array_search('COMBO-PAID-001', $codes);
        $combo2Index = array_search('COMBO-PAID-002', $codes);
        $this->assertLessThan($combo1Index, $combo2Index);
    }
}
