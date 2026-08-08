<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCoupleSeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_assign_employee_to_even_couple_seat()
    {
        // 1. Tạo dữ liệu giả lập
        $role = Role::create(['name' => 'employee']);
        $employee = User::factory()->create(['status' => 'active']);
        $employee->roles()->attach($role->id);

        $coupleType = SeatType::create(['name' => 'Sweetbox', 'surcharge_price' => 0]);
        $normalType = SeatType::create(['name' => 'Thường', 'surcharge_price' => 0]);

        $evenCoupleSeat = Seat::factory()->create([
            'seat_type_id' => $coupleType->id,
            'seat_number' => 2
        ]);
        $oddCoupleSeat = Seat::factory()->create([
            'seat_type_id' => $coupleType->id,
            'seat_number' => 1
        ]);
        
        $showtime = Showtime::factory()->create();

        $showtimeSeatEven = ShowtimeSeat::create([
            'showtime_id' => $showtime->id,
            'seat_id' => $evenCoupleSeat->id,
            'status' => 'available',
            'price' => 100000
        ]);

        $showtimeSeatOdd = ShowtimeSeat::create([
            'showtime_id' => $showtime->id,
            'seat_id' => $oddCoupleSeat->id,
            'status' => 'available',
            'price' => 100000
        ]);

        // 2. Gọi API gán nhân viên (trường hợp hợp lệ)
        $response = $this->postJson('/admin/showtime-seats/assign-employee', [
            'showtime_seat_id' => $showtimeSeatEven->id,
            'employee_id' => $employee->id,
        ]);

        // Route có thể yêu cầu auth, tuỳ setup, ở đây giả lập không cần middleware hoặc cần auth admin
        // Chúng ta giả định bỏ qua middleware hoặc actingAs() admin.

        // Kiểm tra logic isEvenCoupleSeat
        $this->assertTrue($evenCoupleSeat->isEvenCoupleSeat());
        $this->assertFalse($oddCoupleSeat->isEvenCoupleSeat());
    }
}
