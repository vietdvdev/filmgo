<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Lấy dữ liệu KPI dashboard (doanh thu, vé, tỷ lệ lấp đầy, phương thức thanh toán).
     *
     * Tối ưu:
     * - Tính doanh thu vé bằng JOIN trực tiếp thay vì whereHas (tránh sub-query chậm).
     * - Tính doanh thu combo bằng JOIN với aggregate SUM.
     * - Tính tỷ lệ ghế bằng 2 câu COUNT conditional aggregate thay vì 2 query riêng.
     * - Tính phân tách online/counter bằng groupBy SQL thay vì load toàn bộ booking vào memory.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @param  Carbon  $prevStartDate
     * @param  Carbon  $prevEndDate
     * @return array
     */
    public function getKpiData(
        Carbon $startDate,
        Carbon $endDate,
        Carbon $prevStartDate,
        Carbon $prevEndDate
    ): array {
        // ── 1. Doanh thu vé (kỳ này) dùng JOIN thay vì whereHas sub-query ──
        $todayTicketRevenue = (int) DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->sum('booking_details.price');

        // ── Doanh thu combo kỳ này ──
        $todayComboRevenue = (int) DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->sum('booking_combos.subtotal');

        $todayTotalRevenue = $todayTicketRevenue + $todayComboRevenue;

        // ── 2. Doanh thu vé kỳ trước ──
        $yesterdayTicketRevenue = (int) DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
            ->sum('booking_details.price');

        // ── Doanh thu combo kỳ trước ──
        $yesterdayComboRevenue = (int) DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
            ->sum('booking_combos.subtotal');

        $yesterdayTotalRevenue = $yesterdayTicketRevenue + $yesterdayComboRevenue;

        // Tính % tăng trưởng doanh thu
        $revenueGrowth      = $this->calculateGrowth($todayTotalRevenue, $yesterdayTotalRevenue);
        $ticketRevenueGrowth = $this->calculateGrowth($todayTicketRevenue, $yesterdayTicketRevenue);
        $comboRevenueGrowth = $this->calculateGrowth($todayComboRevenue, $yesterdayComboRevenue);

        // ── 3. Tổng số vé đã bán bằng JOIN thay vì whereHas ──
        $todayTicketsCount = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->count();

        $yesterdayTicketsCount = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $ticketsGrowth = $this->calculateGrowth($todayTicketsCount, $yesterdayTicketsCount);

        // ── 4. Tỷ lệ lấp đầy ghế — dùng 2 COUNT query với JOIN để tránh N+1 ──
        $seatStats = DB::table('showtime_seats')
            ->join('showtimes', 'showtime_seats.showtime_id', '=', 'showtimes.id')
            ->whereBetween('showtimes.show_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ])
            /**
             * Dùng SUM(CASE WHEN ...) để đếm tổng ghế và ghế đã đặt trong 1 query duy nhất.
             * Tránh phải chạy 2 COUNT query riêng biệt.
             */
            ->selectRaw('
                COUNT(*) as total_seats,
                SUM(CASE WHEN showtime_seats.status = "booked" THEN 1 ELSE 0 END) as booked_seats
            ')
            ->first();

        $todayTotalSeats  = (int) ($seatStats->total_seats ?? 0);
        $todayBookedSeats = (int) ($seatStats->booked_seats ?? 0);
        $todayOccupancyRate = $todayTotalSeats > 0
            ? round(($todayBookedSeats / $todayTotalSeats) * 100, 2)
            : 0;

        // Tương tự cho kỳ trước
        $prevSeatStats = DB::table('showtime_seats')
            ->join('showtimes', 'showtime_seats.showtime_id', '=', 'showtimes.id')
            ->whereBetween('showtimes.show_date', [
                $prevStartDate->format('Y-m-d'),
                $prevEndDate->format('Y-m-d'),
            ])
            ->selectRaw('
                COUNT(*) as total_seats,
                SUM(CASE WHEN showtime_seats.status = "booked" THEN 1 ELSE 0 END) as booked_seats
            ')
            ->first();

        $yesterdayTotalSeats  = (int) ($prevSeatStats->total_seats ?? 0);
        $yesterdayBookedSeats = (int) ($prevSeatStats->booked_seats ?? 0);
        $yesterdayOccupancyRate = $yesterdayTotalSeats > 0
            ? round(($yesterdayBookedSeats / $yesterdayTotalSeats) * 100, 2)
            : 0;

        $occupancyRateGrowth = round($todayOccupancyRate - $yesterdayOccupancyRate, 2);

        // ── 5. Tỷ lệ thanh toán — dùng GROUP BY SQL thay vì load toàn bộ booking vào memory ──
        $paymentByChannel = Booking::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('channel', DB::raw('SUM(final_total) as revenue'))
            ->groupBy('channel')
            ->pluck('revenue', 'channel');

        $onlineRevenue  = (int) ($paymentByChannel->except('counter')->sum());
        $counterRevenue = (int) ($paymentByChannel->get('counter', 0));

        $totalPaymentRevenue = $onlineRevenue + $counterRevenue;
        $onlinePercentage    = $totalPaymentRevenue > 0
            ? round(($onlineRevenue / $totalPaymentRevenue) * 100, 2)
            : 0;
        $counterPercentage   = $totalPaymentRevenue > 0
            ? round(($counterRevenue / $totalPaymentRevenue) * 100, 2)
            : 0;

        return [
            'revenue' => [
                'today' => [
                    'total' => $todayTotalRevenue,
                    'ticket' => $todayTicketRevenue,
                    'combo' => $todayComboRevenue,
                ],
                'yesterday' => [
                    'total' => $yesterdayTotalRevenue,
                    'ticket' => $yesterdayTicketRevenue,
                    'combo' => $yesterdayComboRevenue,
                ],
                'growth' => [
                    'total_pct' => $revenueGrowth,
                    'ticket_pct' => $ticketRevenueGrowth,
                    'combo_pct' => $comboRevenueGrowth,
                ]
            ],
            'tickets' => [
                'today' => $todayTicketsCount,
                'yesterday' => $yesterdayTicketsCount,
                'growth_pct' => $ticketsGrowth,
            ],
            'occupancy' => [
                'today_rate' => $todayOccupancyRate,
                'yesterday_rate' => $yesterdayOccupancyRate,
                'growth_points' => $occupancyRateGrowth,
                'today_booked_seats' => $todayBookedSeats,
                'today_total_seats' => $todayTotalSeats,
            ],
            'payment_methods' => [
                'online_pct' => $onlinePercentage,
                'counter_pct' => $counterPercentage,
                'online_revenue' => $onlineRevenue,
                'counter_revenue' => $counterRevenue,
            ]
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ doanh thu theo ngày.
     *
     * Tối ưu: Dùng SQL GROUP BY DATE để tính aggregate ở DB thay vì load toàn bộ booking vào PHP.
     * Giúp giảm memory và thời gian xử lý đáng kể khi có nhiều booking.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return array
     */
    public function getRevenueChartData(Carbon $startDate, Carbon $endDate): array
    {
        // Doanh thu vé nhóm theo ngày dùng DATE() aggregate ở DB
        $ticketByDay = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(bookings.created_at) as date'),
                DB::raw('SUM(booking_details.price) as revenue')
            )
            ->groupBy('date')
            ->pluck('revenue', 'date');

        // Doanh thu combo nhóm theo ngày
        $comboByDay = DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(bookings.created_at) as date'),
                DB::raw('SUM(booking_combos.subtotal) as revenue')
            )
            ->groupBy('date')
            ->pluck('revenue', 'date');

        // Tạo mảng kết quả điền đủ các ngày trong khoảng (kể cả ngày không có doanh thu = 0)
        $labels     = [];
        $ticketData = [];
        $comboData  = [];

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
     * Helper tính tỷ lệ tăng trưởng giữa 2 kỳ.
     *
     * @param  int|float  $current   Giá trị kỳ này
     * @param  int|float  $previous  Giá trị kỳ trước
     * @return float
     */
    public function calculateGrowth(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Thống kê doanh thu và số lượng vé bán ra theo từng phim.
     * Sử dụng JOIN để kết nối các bảng movies, showtimes, bookings và booking_details.
     * Chỉ tính những đơn hàng đã thanh toán (payment_status = paid).
     * Dùng DB::raw SUM và COUNT để tính toán trực tiếp tại CSDL nhằm tối ưu bộ nhớ.
     * Sắp xếp theo tổng doanh thu giảm dần để hiển thị Top phim.
     * 
     * @param Carbon $startDate Ngày bắt đầu
     * @param Carbon $endDate Ngày kết thúc
     * @return \Illuminate\Support\Collection Danh sách phim cùng dữ liệu doanh thu
     */
    public function getMovieRevenueStatistics(Carbon $startDate, Carbon $endDate)
    {
        return DB::table('movies')
            ->join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
            ->join('bookings', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('booking_details', 'bookings.id', '=', 'booking_details.booking_id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                'movies.id',
                'movies.title',
                DB::raw('COUNT(booking_details.id) as tickets_count'),
                DB::raw('SUM(booking_details.price) as total_revenue')
            )
            ->groupBy('movies.id', 'movies.title')
            ->orderByDesc('total_revenue')
            ->get();
    }
}
