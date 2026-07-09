<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ShowtimeSeat;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExpireBookingsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('bookings', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('showtime_id')->nullable();
            $table->string('booking_code')->unique();
            $table->integer('total_amount')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('booking_status')->default('pending');
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets', function ($table) {
            $table->id();
            $table->unsignedBigInteger('booking_detail_id')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('ticket_status')->default('unused');
            $table->timestamp('checked_in_at')->nullable();
        });

        Schema::create('showtime_seats', function ($table) {
            $table->id();
            $table->unsignedBigInteger('showtime_id')->nullable();
            $table->unsignedBigInteger('seat_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('available');
            $table->integer('price')->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
    }

    public function test_expired_pending_booking_is_canceled_and_seat_is_released(): void
    {
        $booking = Booking::create([
            'user_id' => 1,
            'showtime_id' => 1,
            'booking_code' => 'FG-EXPIRED-1',
            'total_amount' => 100000,
            'discount_amount' => 0,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expired_at' => now()->subMinute(),
        ]);

        $ticket = Ticket::create([
            'booking_detail_id' => 1,
            'qr_code' => 'TKT-TEST-1',
            'ticket_status' => 'unused',
        ]);

        $showtimeSeat = ShowtimeSeat::create([
            'showtime_id' => 1,
            'seat_id' => 1,
            'user_id' => 1,
            'status' => 'booked',
            'price' => 100000,
            'locked_at' => now()->subMinute(),
            'expires_at' => now()->subSecond(),
        ]);

        Artisan::call('bookings:expire');

        $booking->refresh();
        $ticket->refresh();
        $showtimeSeat->refresh();

        $this->assertSame('cancelled', $booking->booking_status);
        $this->assertSame('failed', $booking->payment_status);
        $this->assertSame('cancelled', $ticket->ticket_status);
        $this->assertSame('available', $showtimeSeat->status);
        $this->assertNull($showtimeSeat->user_id);
    }
}
