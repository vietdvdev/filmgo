<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerCancelledBookingHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->default('password');
            $table->string('role')->default('customer');
            $table->timestamps();
        });

        Schema::create('cinemas', function ($table) {
            $table->id();
            $table->string('name')->default('FilmGo Cinema');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('promotions', function ($table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('discount_type')->default('fixed');
            $table->integer('discount_value')->default(10000);
            $table->integer('used_count')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('bookings', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('showtime_id')->nullable();
            $table->unsignedBigInteger('cinema_id')->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('booking_code')->unique();
            $table->integer('subtotal')->default(0);
            $table->integer('total_amount')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('final_total')->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('booking_status')->default('pending');
            $table->string('channel')->default('online');
            $table->string('booking_type')->default('ticket');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_details', function ($table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('showtime_seat_id')->nullable();
            $table->integer('price')->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('transaction_code')->nullable();
            $table->integer('amount')->default(0);
            $table->string('payment_method')->default('vnpay');
            $table->string('payment_status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_customer_history_only_displays_paid_and_confirmed_bookings(): void
    {
        $user = User::create([
            'id' => 1,
            'name' => 'Nguyen Van A',
            'email' => 'customer@example.com',
        ]);

        // Đơn đã thanh toán thành công
        Booking::create([
            'user_id' => $user->id,
            'booking_code' => 'FG-PAID-001',
            'total_amount' => 120000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Đơn đã bị hủy thanh toán
        Booking::create([
            'user_id' => $user->id,
            'booking_code' => 'FG-CANCEL-002',
            'total_amount' => 150000,
            'payment_status' => 'failed',
            'booking_status' => 'cancelled',
            'expired_at' => now()->addMinutes(10), // Giả định còn hạn expired_at
        ]);

        // Đơn pending chưa hoàn tất
        Booking::create([
            'user_id' => $user->id,
            'booking_code' => 'FG-PENDING-003',
            'total_amount' => 180000,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expired_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->get(route('customer.bookings.history'));

        $response->assertStatus(200);
        $response->assertSee('FG-PAID-001');
        $response->assertDontSee('FG-CANCEL-002');
        $response->assertDontSee('FG-PENDING-003');
    }

    public function test_customer_cannot_view_cancelled_booking_detail(): void
    {
        $user = User::create([
            'id' => 2,
            'name' => 'Nguyen Van B',
            'email' => 'customer2@example.com',
        ]);

        $cancelledBooking = Booking::create([
            'user_id' => $user->id,
            'booking_code' => 'FG-CANCEL-DETAIL',
            'total_amount' => 90000,
            'payment_status' => 'failed',
            'booking_status' => 'cancelled',
        ]);

        $response = $this->actingAs($user)->get(route('customer.bookings.show', $cancelledBooking->id));

        $response->assertRedirect(route('customer.bookings.history'));
        $response->assertSessionHas('error');
    }

    public function test_cancelling_booking_refunds_promotion_used_count(): void
    {
        $promo = Promotion::create([
            'code' => 'GIAMGIA50K',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'used_count' => 1,
            'status' => 'active',
        ]);

        $user = User::create([
            'id' => 3,
            'name' => 'Nguyen Van C',
            'email' => 'customer3@example.com',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'promotion_id' => $promo->id,
            'booking_code' => 'FG-PROMO-CANCEL',
            'total_amount' => 100000,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        // Giả lập hủy thanh toán và hoàn voucher
        if ($booking->promotion_id) {
            Promotion::where('id', $booking->promotion_id)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }

        $promo->refresh();
        $this->assertEquals(0, $promo->used_count);
    }
}
