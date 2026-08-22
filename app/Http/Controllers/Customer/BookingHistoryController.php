<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingHistoryController extends Controller
{
    /**
     * Danh sách lịch sử đặt vé của khách hàng hiện tại.
     *
     * Tối ưu:
     * - withQueryString() để giữ nguyên query params khi chuyển trang.
     * - Chỉ eager load relations cần thiết cho list view (bỏ combos/promotions).
     * - Dùng select tường minh cho user để tránh load toàn bộ cột.
     */
    public function index()
    {
        $bookings = Booking::with([
                'showtime.movie:id,title,poster,duration',
                'showtime.room.cinema:id,name',
                'cinema:id,name',
                'bookingDetails',
                'payments:id,booking_id,payment_method,payment_status',
            ])
            ->where('user_id', Auth::id())
            ->customerHistory()             // Chỉ lấy các đơn đã thanh toán thành công, ẩn triệt để đơn hủy/thất bại
            ->latest()
            ->paginate(8)
            ->withQueryString();    // Giữ nguyên query params khi phân trang

        return view('customer.bookings.history', compact('bookings'));
    }

    /**
     * Chi tiết một đơn đặt vé.
     * Eager load đầy đủ thông tin cần thiết để hiển thị trang chi tiết.
     */
    public function show($id)
    {
        $booking = Booking::with([
                'showtime.movie',
                'showtime.room.cinema',
                'cinema:id,name',
                'bookingDetails.showtimeSeat.seat.seatType',
                'combos',
                'comboItems.comboItem',
                'promotions',
                'payments',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Chặn hiển thị chi tiết đơn hàng đã bị hủy hoặc thanh toán thất bại
        if ($booking->booking_status === 'cancelled' || $booking->payment_status === 'failed') {
            return redirect()->route('customer.bookings.history')
                ->with('error', 'Đơn hàng này đã bị hủy hoặc chưa hoàn tất thanh toán.');
        }

        return view('customer.bookings.detail', compact('booking'));
    }
}
