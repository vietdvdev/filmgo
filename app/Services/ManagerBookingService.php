<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\Room;
use Illuminate\Pagination\LengthAwarePaginator;

class ManagerBookingService
{
    /**
     * Lấy danh sách đơn hàng thuộc rạp quản lý kèm theo bộ lọc.
     *
     * @param int $cinemaId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getList(int $cinemaId, array $filters): LengthAwarePaginator
    {
        $query = Booking::with([
            'user:id,full_name,email,phone',
            'staff:id,full_name,email',
            'showtime.movie:id,title,poster',
            'showtime.room:id,room_name,cinema_id',
            'cinema:id,name',
            'bookingDetails.showtimeSeat.seat',
            'combos:id,combo_name',
            'comboItems.comboItem:id,name',
            'payments:id,booking_id,payment_method,payment_status,amount',
        ])
        ->where(function ($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId)
              ->orWhereHas('showtime.room', fn($r) => $r->where('cinema_id', $cinemaId));
        })
        ->excludeExpired();

        // 1. Tìm kiếm (Mã đơn, Tên khách, Email, Số điện thoại)
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('booking_code', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        // 2. Lọc theo Phim
        if (!empty($filters['movie_id'])) {
            $query->whereHas('showtime', fn($q) => $q->where('movie_id', $filters['movie_id']));
        }

        // 3. Lọc theo Phòng chiếu
        if (!empty($filters['room_id'])) {
            $query->whereHas('showtime', fn($q) => $q->where('room_id', $filters['room_id']));
        }

        // 4. Lọc theo Trạng thái thanh toán
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // 5. Lọc theo Loại đơn
        if (!empty($filters['booking_type'])) {
            if ($filters['booking_type'] === 'combo_only') {
                $query->where('booking_type', 'combo_only');
            } elseif ($filters['booking_type'] === 'ticket') {
                $query->where('booking_type', '!=', 'combo_only');
            }
        }

        // 6. Lọc theo Kênh đặt
        if (!empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        // 7. Lọc theo Trạng thái in vé
        if (!empty($filters['print_status'])) {
            if ($filters['print_status'] === 'printed') {
                $query->whereNotNull('printed_at');
            } elseif ($filters['print_status'] === 'not_printed') {
                $query->whereNull('printed_at');
            }
        }

        // 8. Lọc theo Ngày chiếu
        if (!empty($filters['show_date_from'])) {
            $query->whereHas('showtime', fn($q) => $q->where('show_date', '>=', $filters['show_date_from']));
        }
        if (!empty($filters['show_date_to'])) {
            $query->whereHas('showtime', fn($q) => $q->where('show_date', '<=', $filters['show_date_to']));
        }

        // 9. Lọc theo Ngày tạo đơn
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // 10. Sắp xếp
        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'oldest'      => $query->oldest(),
            'amount_asc'  => $query->orderBy('final_total', 'asc'),
            'amount_desc' => $query->orderBy('final_total', 'desc'),
            default       => $query->latest(),
        };

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Lấy các chỉ số thống kê nhanh (KPI cards) cho rạp.
     *
     * @param int $cinemaId
     * @param array $filters
     * @return array
     */
    public function getSummaryStats(int $cinemaId, array $filters = []): array
    {
        $baseQuery = Booking::where(function ($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId)
              ->orWhereHas('showtime.room', fn($r) => $r->where('cinema_id', $cinemaId));
        })->excludeExpired();

        if (!empty($filters['created_from'])) {
            $baseQuery->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $baseQuery->whereDate('created_at', '<=', $filters['created_to']);
        }

        $totalOrders = (clone $baseQuery)->count();
        $paidQuery   = (clone $baseQuery)->where('payment_status', 'paid');
        $totalRevenue = (int) $paidQuery->sum('final_total');
        $counterOrders = (clone $baseQuery)->where('channel', 'counter')->count();
        $onlineOrders  = (clone $baseQuery)->where('channel', '!=', 'counter')->count();
        $printedOrders = (clone $baseQuery)->whereNotNull('printed_at')->count();

        return [
            'total_orders'   => $totalOrders,
            'total_revenue'  => $totalRevenue,
            'counter_orders' => $counterOrders,
            'online_orders'  => $onlineOrders,
            'printed_orders' => $printedOrders,
        ];
    }

    /**
     * Lấy chi tiết đơn hàng (kèm kiểm tra bảo mật thuộc rạp).
     *
     * @param int $id
     * @param int $cinemaId
     * @return Booking
     */
    public function getDetail(int $id, int $cinemaId): Booking
    {
        return Booking::with([
            'user',
            'staff',
            'showtime.movie',
            'showtime.room.cinema',
            'cinema',
            'bookingDetails.showtimeSeat.seat.seatType',
            'bookingDetails.ticket',
            'combos',
            'comboItems.comboItem',
            'promotion',
            'payments',
        ])
        ->where(function ($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId)
              ->orWhereHas('showtime.room', fn($r) => $r->where('cinema_id', $cinemaId));
        })
        ->findOrFail($id);
    }

    /**
     * Lấy danh sách phim đã / đang có suất chiếu tại rạp.
     *
     * @param int $cinemaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMoviesInCinema(int $cinemaId)
    {
        return Movie::whereHas('showtimes.room', fn($r) => $r->where('cinema_id', $cinemaId))
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    /**
     * Lấy danh sách phòng chiếu thuộc rạp.
     *
     * @param int $cinemaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomsInCinema(int $cinemaId)
    {
        return Room::where('cinema_id', $cinemaId)
            ->orderBy('room_name')
            ->get(['id', 'room_name']);
    }
}
