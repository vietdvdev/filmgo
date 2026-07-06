<?php

namespace Tests\Feature;

use App\Http\Controllers\Customer\BookingController;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class BookingPaymentTest extends TestCase
{
    public function test_momo_payment_failure_redirects_back_with_error_message()
    {
        $user = new User();
        $user->id = 1;
        $user->email = 'customer@example.com';
        $user->name = 'Customer';

        Auth::shouldReceive('id')->andReturn($user->id);

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->forceFill([
            'id' => 1,
            'booking_code' => 'ABC123',
            'total_amount' => 100000,
        ]);
        $booking->shouldReceive('update')->andReturn(true);

        $bookingService = Mockery::mock(BookingService::class);
        $bookingService->shouldReceive('createBooking')->once()->andReturn($booking);

        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('createMoMoUrl')->once()->andThrow(new Exception('Momo gateway error'));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $controller = new BookingController($bookingService, $paymentService);

        session()->put('booking.123.seat_ids', [1]);
        session()->put('booking.123.combos', []);

        putenv('MOMO_FALLBACK_URL=https://momo.vn');

        $request = Request::create('/booking/showtime/123/confirm', 'POST', [
            'payment_method' => 'momo',
        ]);

        $response = $controller->confirm($request, 123);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirection());
        $this->assertStringContainsString('/booking/payment/qr', $response->headers->get('Location'));
    }

    public function test_vnpay_redirects_directly_to_sandbox_gateway_with_selected_bank()
    {
        $user = new User();
        $user->id = 2;
        $user->email = 'customer2@example.com';
        $user->name = 'Customer 2';

        Auth::shouldReceive('id')->andReturn($user->id);

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->forceFill([
            'id' => 2,
            'booking_code' => 'XYZ789',
            'total_amount' => 200000,
        ]);
        $booking->shouldReceive('update')->andReturn(true);

        $bookingService = Mockery::mock(BookingService::class);
        $bookingService->shouldReceive('createBooking')->once()->andReturn($booking);

        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('createVnPayUrl')->once()->with('XYZ789', 200000, 'NCB')->andReturn('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?bankCode=NCB');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $controller = new BookingController($bookingService, $paymentService);

        session()->put('booking.123.seat_ids', [1]);
        session()->put('booking.123.combos', []);

        $request = Request::create('/booking/showtime/123/confirm', 'POST', [
            'payment_method' => 'vnpay',
            'bank_code' => 'NCB',
        ]);

        $response = $controller->confirm($request, 123);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirection());
        $this->assertStringContainsString('sandbox.vnpayment.vn', $response->headers->get('Location'));
        $this->assertStringContainsString('bankCode=NCB', $response->headers->get('Location'));
    }
}
