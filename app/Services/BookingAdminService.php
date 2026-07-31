<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingAdminService
{
    public function getList(array $filters): LengthAwarePaginator
    {
        /**
         * Eager load chỉ những quan hệ cần thiết ở danh sách (index view).
         * Loại bỏ 'bookingDetails.showtimeSeat.seat' — chỉ cần ở trang chi tiết.
         * Dùng select cột tường minh để tránh load toàn bộ cột vào memory.
         */
        $query = Booking::with([
            'user:id,full_name,email,phone',
            'showtime.movie:id,title',
            'showtime.room.cinema:id,name',
            'payments:id,booking_id,payment_method,payment_status,amount',
        ])->excludeExpired();  // Ẩn các đơn đã bị hủy tự động do hết hạn giữ ghế

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('booking_code', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        if (!empty($filters['cinema_id'])) {
            $query->whereHas('showtime.room', fn($q) => $q->where('cinema_id', $filters['cinema_id']));
        }

        if (!empty($filters['movie_id'])) {
            $query->whereHas('showtime', fn($q) => $q->where('movie_id', $filters['movie_id']));
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['show_date_from'])) {
            $query->whereHas('showtime', fn($q) => $q->where('show_date', '>=', $filters['show_date_from']));
        }

        if (!empty($filters['show_date_to'])) {
            $query->whereHas('showtime', fn($q) => $q->where('show_date', '<=', $filters['show_date_to']));
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'oldest'        => $query->oldest(),
            'amount_asc'    => $query->orderBy('final_total', 'asc'),
            'amount_desc'   => $query->orderBy('final_total', 'desc'),
            default         => $query->latest(),
        };

        return $query->paginate(15)->withQueryString();
    }

    public function getDetail(int $id): Booking
    {
        return Booking::with([
            'user',
            'showtime.movie',
            'showtime.room.cinema',
            'bookingDetails.showtimeSeat.seat',
            'combos',
            'promotion',
            'payments',
        ])->findOrFail($id);
    }

    public function getCinemas()
    {
        return Cinema::orderBy('name')->get(['id', 'name']);
    }

    public function getMovies()
    {
        return Movie::orderBy('title')->get(['id', 'title']);
    }
}
