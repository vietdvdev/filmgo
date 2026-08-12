<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManagerDashboardService
{
    /**
     * KPI data lọc theo cinema_id từ bookings.cinema_id (bao gồm cả đơn combo_only).
     */
    public function getKpiData(int $cinemaId, Carbon $startDate, Carbon $endDate, Carbon $prevStartDate, Carbon $prevEndDate): array
    {
        // ── Doanh thu vé kỳ này ──
        $todayTicketRevenue = (int) DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->sum('booking_details.price');

        // ── Doanh thu combo kỳ này (bao gồm cả đơn combo_only) ──
        $todayComboRevenue = (int) DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->sum('booking_combos.subtotal');

        $todayTotalRevenue = $todayTicketRevenue + $todayComboRevenue;

        // ── Doanh thu kỳ trước ──
        $prevTicketRevenue = (int) DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
            ->sum('booking_details.price');

        $prevComboRevenue = (int) DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
            ->sum('booking_combos.subtotal');

        $prevTotalRevenue = $prevTicketRevenue + $prevComboRevenue;

        // ── Số vé đã bán (chỉ đơn ticket, không tính combo_only) ──
        $todayTickets = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->count();

        $prevTickets = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
            ->count();

        // ── Tỷ lệ lấp đầy ghế (chỉ tính suất chiếu thuộc rạp) ──
        $seatStats = DB::table('showtime_seats')
            ->join('showtimes', 'showtime_seats.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->where('rooms.cinema_id', $cinemaId)
            ->whereBetween('showtimes.show_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('COUNT(*) as total_seats, SUM(CASE WHEN showtime_seats.status = "booked" THEN 1 ELSE 0 END) as booked_seats')
            ->first();

        $totalSeats    = (int) ($seatStats->total_seats ?? 0);
        $bookedSeats   = (int) ($seatStats->booked_seats ?? 0);
        $occupancyRate = $totalSeats > 0 ? round(($bookedSeats / $totalSeats) * 100, 2) : 0;

        $prevSeatStats = DB::table('showtime_seats')
            ->join('showtimes', 'showtime_seats.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->where('rooms.cinema_id', $cinemaId)
            ->whereBetween('showtimes.show_date', [$prevStartDate->format('Y-m-d'), $prevEndDate->format('Y-m-d')])
            ->selectRaw('COUNT(*) as total_seats, SUM(CASE WHEN showtime_seats.status = "booked" THEN 1 ELSE 0 END) as booked_seats')
            ->first();

        $prevOccupancyRate = ($prevSeatStats->total_seats ?? 0) > 0
            ? round(($prevSeatStats->booked_seats / $prevSeatStats->total_seats) * 100, 2)
            : 0;

        // ── Phương thức thanh toán (dùng bookings.cinema_id, bao gồm combo_only) ──
        $paymentByChannel = DB::table('bookings')
            ->where('cinema_id', $cinemaId)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('channel', DB::raw('SUM(final_total) as revenue'))
            ->groupBy('channel')
            ->pluck('revenue', 'channel');

        $onlineRevenue  = (int) $paymentByChannel->except('counter')->sum();
        $counterRevenue = (int) ($paymentByChannel->get('counter', 0));
        $totalPay       = $onlineRevenue + $counterRevenue;
        $onlinePct      = $totalPay > 0 ? round(($onlineRevenue / $totalPay) * 100, 2) : 0;

        $calcGrowth = fn($cur, $prev) => $prev == 0 ? ($cur > 0 ? 100 : 0) : round((($cur - $prev) / $prev) * 100, 2);

        return [
            'revenue' => [
                'today'     => ['total' => $todayTotalRevenue, 'ticket' => $todayTicketRevenue, 'combo' => $todayComboRevenue],
                'yesterday' => ['total' => $prevTotalRevenue],
                'growth'    => ['total_pct' => $calcGrowth($todayTotalRevenue, $prevTotalRevenue)],
            ],
            'tickets' => [
                'today'      => $todayTickets,
                'yesterday'  => $prevTickets,
                'growth_pct' => $calcGrowth($todayTickets, $prevTickets),
            ],
            'occupancy' => [
                'today_rate'         => $occupancyRate,
                'growth_points'      => round($occupancyRate - $prevOccupancyRate, 2),
                'today_booked_seats' => $bookedSeats,
                'today_total_seats'  => $totalSeats,
            ],
            'payment_methods' => [
                'online_pct'      => $onlinePct,
                'online_revenue'  => $onlineRevenue,
                'counter_revenue' => $counterRevenue,
            ],
        ];
    }

    /**
     * Doanh thu theo ngày lọc theo bookings.cinema_id.
     */
    public function getRevenueChartData(int $cinemaId, Carbon $startDate, Carbon $endDate): array
    {
        $ticketByDay = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(bookings.created_at) as date'), DB::raw('SUM(booking_details.price) as revenue'))
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $comboByDay = DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(bookings.created_at) as date'), DB::raw('SUM(booking_combos.subtotal) as revenue'))
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $labels = $ticketData = $comboData = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr      = $current->format('Y-m-d');
            $labels[]     = $current->format('d/m');
            $ticketData[] = (int) ($ticketByDay->get($dateStr, 0));
            $comboData[]  = (int) ($comboByDay->get($dateStr, 0));
            $current->addDay();
        }

        return compact('labels', 'ticketData', 'comboData');
    }

    /**
     * Top phim theo doanh thu — chỉ đơn ticket (combo_only không có phim).
     */
    public function getTopMoviesData(int $cinemaId, Carbon $startDate, Carbon $endDate): array
    {
        $totalTickets = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'ticket')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->count();

        $topMovies = DB::table('movies')
            ->join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
            ->join('bookings', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('booking_details', 'bookings.id', '=', 'booking_details.booking_id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'ticket')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                'movies.id',
                'movies.title',
                DB::raw('COUNT(booking_details.id) as tickets_count'),
                DB::raw('SUM(booking_details.price) as revenue')
            )
            ->groupBy('movies.id', 'movies.title')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn($movie) => [
                'title'         => $movie->title,
                'tickets_count' => (int) $movie->tickets_count,
                'revenue'       => (int) $movie->revenue,
                'percentage'    => $totalTickets > 0 ? round(($movie->tickets_count / $totalTickets) * 100, 2) : 0,
            ]);

        return ['total_tickets_in_period' => $totalTickets, 'top_movies' => $topMovies];
    }

    /**
     * Đơn đặt vé gần đây theo khoảng ngày.
     */
    public function getRecentBookings(int $cinemaId, Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return DB::table('bookings')
            ->leftJoin('users as u', 'bookings.user_id', '=', 'u.id')
            ->leftJoin('users as s', 'bookings.staff_id', '=', 's.id')
            ->leftJoin('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->leftJoin('movies', 'showtimes.movie_id', '=', 'movies.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'ticket')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                'bookings.id',
                'bookings.booking_code',
                DB::raw('COALESCE(u.full_name, s.full_name, "Khách vãng lai") as customer_name'),
                'movies.title as movie_title',
                'bookings.final_total',
                'bookings.channel',
                'bookings.created_at',
                DB::raw('(SELECT COUNT(*) FROM booking_details WHERE booking_details.booking_id = bookings.id) as ticket_count')
            )
            ->orderByDesc('bookings.created_at')
            ->limit($limit)
            ->get()
            ->map(fn($b) => [
                'id'            => $b->id,
                'booking_code'  => $b->booking_code,
                'customer_name' => $b->customer_name,
                'movie_title'   => $b->movie_title ?? '—',
                'ticket_count'  => (int) $b->ticket_count,
                'final_total'   => (int) $b->final_total,
                'channel'       => $b->channel,
                'created_at'    => Carbon::parse($b->created_at)->format('d/m H:i'),
            ])
            ->toArray();
    }

    /**
     * Đơn đặt bắp nước gần đây theo khoảng ngày.
     */
    public function getRecentComboBookings(int $cinemaId, Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return DB::table('bookings')
            ->leftJoin('users as u', 'bookings.user_id', '=', 'u.id')
            ->leftJoin('users as s', 'bookings.staff_id', '=', 's.id')
            ->where('bookings.cinema_id', $cinemaId)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'combo_only')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                'bookings.id',
                'bookings.booking_code',
                DB::raw('COALESCE(u.full_name, s.full_name, "Khách vãng lai") as customer_name'),
                'bookings.final_total',
                'bookings.channel',
                'bookings.created_at',
                DB::raw('(SELECT SUM(quantity) FROM booking_combos WHERE booking_combos.booking_id = bookings.id) as combo_qty')
            )
            ->orderByDesc('bookings.created_at')
            ->limit($limit)
            ->get()
            ->map(fn($b) => [
                'id'            => $b->id,
                'booking_code'  => $b->booking_code,
                'customer_name' => $b->customer_name,
                'combo_qty'     => (int) ($b->combo_qty ?? 0),
                'final_total'   => (int) $b->final_total,
                'channel'       => $b->channel,
                'created_at'    => Carbon::parse($b->created_at)->format('d/m H:i'),
            ])
            ->toArray();
    }

    /**
     * Suất chiếu hôm nay lọc theo cinema_id qua rooms.
     */
    public function getTodayShowtimes(int $cinemaId): array
    {
        $today   = today()->format('Y-m-d');
        $nowTime = now()->format('H:i:s');

        $showtimes = DB::table('showtimes')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->where('rooms.cinema_id', $cinemaId)
            ->whereDate('showtimes.show_date', $today)
            ->where('showtimes.start_time', '>=', $nowTime)
            ->where('showtimes.status', '!=', 'cancelled')
            ->select('showtimes.id', 'movies.title as movie_title', 'rooms.room_name', 'showtimes.start_time')
            ->orderBy('showtimes.start_time')
            ->get();

        return $showtimes->map(function ($st) {
            $seatStats = DB::table('showtime_seats')
                ->where('showtime_id', $st->id)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "booked" THEN 1 ELSE 0 END) as booked')
                ->first();

            $total  = (int) ($seatStats->total ?? 0);
            $booked = (int) ($seatStats->booked ?? 0);

            return [
                'id'                   => $st->id,
                'movie_title'          => $st->movie_title,
                'room_name'            => $st->room_name,
                'start_time'           => Carbon::parse($st->start_time)->format('H:i'),
                'booked_seats'         => $booked,
                'total_seats'          => $total,
                'occupancy_percentage' => $total > 0 ? round(($booked / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }
}
