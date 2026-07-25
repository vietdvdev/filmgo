<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffBookingService
{
    /**
     * Truy vấn danh sách đơn đặt vé trong ngày theo Rạp phân công của nhân viên.
     * 
     * Tối ưu hiệu suất truy vấn (Eager Loading):
     * - 'user': Lấy thông tin khách hàng (họ tên, SĐT, email).
     * - 'showtime.movie': Lấy thông tin phim (tên phim, ảnh, thời lượng).
     * - 'showtime.room': Lấy thông tin phòng chiếu thuộc rạp.
     * - 'bookingDetails.showtimeSeat.seat': Lấy danh sách số ghế khách đặt.
     * - 'payments': Lấy lịch sử/trạng thái thanh toán đơn hàng.
     *
     * @param int $cinemaId ID của rạp mà nhân viên được phân công làm việc.
     * @param string|null $date Ngày chiếu dạng Y-m-d (mặc định lấy ngày hôm nay).
     * @param int $perPage Số lượng phần tử hiển thị trên một trang.
     * @return LengthAwarePaginator
     */
    public function getDailyBookingsByCinema(int $cinemaId, ?string $date = null, int $perPage = 15): LengthAwarePaginator
    {
        // 1. Xác định ngày chiếu cần lọc (Nếu không truyền date -> Mặc định ngày hôm nay)
        $targetDate = $date ?? now()->toDateString();

        return Booking::query()
            // 2. Tối ưu hiệu suất: Load trước tất cả quan hệ cần thiết truyền ra View (Eager Loading)
            ->with([
                'user',
                'showtime.movie',
                'showtime.room',
                'bookingDetails.showtimeSeat.seat',
                'payments',
            ])
            // 3. Ràng buộc an toàn dữ liệu: Chỉ lấy các đơn đặt vé thuộc rạp của nhân viên
            ->whereHas('showtime.room', function ($query) use ($cinemaId) {
                $query->where('cinema_id', $cinemaId);
            })
            // 4. Lọc danh sách theo ngày chiếu của suất chiếu (show_date)
            ->whereHas('showtime', function ($query) use ($targetDate) {
                $query->whereDate('show_date', $targetDate);
            })
            // 5. Sắp xếp danh sách theo giờ chiếu (start_time) tăng dần
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->orderBy('showtimes.start_time', 'asc')
            ->select('bookings.*')
            // 6. Phân trang dữ liệu kết hợp duy trì tham số query string trên thanh địa chỉ
            ->paginate($perPage)
            ->withQueryString();
    }
}
