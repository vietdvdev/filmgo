<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Movie;
use App\Models\ConflictPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /**
     * Trả về view của dashboard admin (truyền thống)
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * 1. API Thống kê KPI (Endpoint: /kpis)
     * Nhận tham số start_date và end_date để lọc động. 
     * Tự động tính toán khoảng thời gian so sánh (quá khứ) có cùng độ dài để tính % tăng trưởng.
     * Sử dụng cache động theo khoảng thời gian.
     */
    public function kpis(Request $request)
    {
        $startDateInput = $request->query('start_date');
        $endDateInput = $request->query('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Mặc định là ngày hôm nay
            $startDate = today()->startOfDay();
            $endDate = today()->endOfDay();
        }

        // Độ dài khoảng thời gian hiện tại để lùi lại khoảng thời gian so sánh tương đương
        $diffInDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();
        $prevEndDate = $startDate->copy()->subDay()->endOfDay();

        $cacheKey = 'admin_dashboard_kpis_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $prevStartDate, $prevEndDate) {
            // --- 1.1 Tổng doanh thu ---
            // Doanh thu vé kỳ này
            $todayTicketRevenue = (int) BookingDetail::whereHas('booking', function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid')->whereBetween('created_at', [$startDate, $endDate]);
            })->sum('price');

            // Doanh thu combo kỳ này
            $todayComboRevenue = (int) DB::table('booking_combos')
                ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
                ->where('bookings.payment_status', 'paid')
                ->whereBetween('bookings.created_at', [$startDate, $endDate])
                ->sum('booking_combos.subtotal');

            $todayTotalRevenue = $todayTicketRevenue + $todayComboRevenue;

            // Doanh thu vé kỳ trước
            $yesterdayTicketRevenue = (int) BookingDetail::whereHas('booking', function ($q) use ($prevStartDate, $prevEndDate) {
                $q->where('payment_status', 'paid')->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
            })->sum('price');

            // Doanh thu combo kỳ trước
            $yesterdayComboRevenue = (int) DB::table('booking_combos')
                ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
                ->where('bookings.payment_status', 'paid')
                ->whereBetween('bookings.created_at', [$prevStartDate, $prevEndDate])
                ->sum('booking_combos.subtotal');

            $yesterdayTotalRevenue = $yesterdayTicketRevenue + $yesterdayComboRevenue;

            // Tính % tăng trưởng doanh thu
            $revenueGrowth = $this->calculateGrowth($todayTotalRevenue, $yesterdayTotalRevenue);
            $ticketRevenueGrowth = $this->calculateGrowth($todayTicketRevenue, $yesterdayTicketRevenue);
            $comboRevenueGrowth = $this->calculateGrowth($todayComboRevenue, $yesterdayComboRevenue);

            // --- 1.2 Tổng số vé đã bán (status = booked trong booking_details của đơn đã paid) ---
            $todayTicketsCount = BookingDetail::whereHas('booking', function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid')->whereBetween('created_at', [$startDate, $endDate]);
            })->count();

            $yesterdayTicketsCount = BookingDetail::whereHas('booking', function ($q) use ($prevStartDate, $prevEndDate) {
                $q->where('payment_status', 'paid')->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
            })->count();

            $ticketsGrowth = $this->calculateGrowth($todayTicketsCount, $yesterdayTicketsCount);

            // --- 1.3 Tỷ lệ lấp đầy rạp (% ghế đã bán / tổng ghế các suất chiếu trong kỳ) ---
            // Kỳ này
            $todayShowtimeSeats = ShowtimeSeat::whereHas('showtime', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('show_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            });
            $todayTotalSeats = (clone $todayShowtimeSeats)->count();
            $todayBookedSeats = (clone $todayShowtimeSeats)->where('status', 'booked')->count();
            $todayOccupancyRate = $todayTotalSeats > 0 ? round(($todayBookedSeats / $todayTotalSeats) * 100, 2) : 0;

            // Kỳ trước
            $yesterdayShowtimeSeats = ShowtimeSeat::whereHas('showtime', function ($q) use ($prevStartDate, $prevEndDate) {
                $q->whereBetween('show_date', [$prevStartDate->format('Y-m-d'), $prevEndDate->format('Y-m-d')]);
            });
            $yesterdayTotalSeats = (clone $yesterdayShowtimeSeats)->count();
            $yesterdayBookedSeats = (clone $yesterdayShowtimeSeats)->where('status', 'booked')->count();
            $yesterdayOccupancyRate = $yesterdayTotalSeats > 0 ? round(($yesterdayBookedSeats / $yesterdayTotalSeats) * 100, 2) : 0;

            // Tăng trưởng tỷ lệ lấp đầy (đơn vị: điểm phần trăm)
            $occupancyRateGrowth = round($todayOccupancyRate - $yesterdayOccupancyRate, 2);

            // --- 1.4 Tỷ lệ thanh toán: % Online (momo, vnpay) vs % Tại quầy (pos/counter) ---
            $todayBookings = Booking::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $onlineRevenue = 0;
            $counterRevenue = 0;

            foreach ($todayBookings as $b) {
                if ($b->channel === 'counter') {
                    $counterRevenue += $b->final_total;
                } else {
                    $onlineRevenue += $b->final_total;
                }
            }

            $totalPaymentRevenue = $onlineRevenue + $counterRevenue;
            $onlinePercentage = $totalPaymentRevenue > 0 ? round(($onlineRevenue / $totalPaymentRevenue) * 100, 2) : 0;
            $counterPercentage = $totalPaymentRevenue > 0 ? round(($counterRevenue / $totalPaymentRevenue) * 100, 2) : 0;

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
        });

        return response()->json($data);
    }

    /**
     * 2.1 API Biểu đồ Doanh thu (charts/revenue)
     * Trả về doanh thu vé và combo nhóm theo từng ngày từ start_date đến end_date.
     * Mặc định là 7 ngày qua.
     */
    public function chartsRevenue(Request $request)
    {
        $startDateInput = $request->query('start_date');
        $endDateInput = $request->query('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Mặc định là 7 ngày gần đây
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Query gom các booking thành công của kỳ, eager load để chống N+1 query
        $bookings = Booking::with(['bookingDetails', 'combos'])
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($booking) {
                return $booking->created_at->format('Y-m-d');
            });

        $labels = [];
        $ticketData = [];
        $comboData = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');

            $dayBookings = $bookings->get($dateStr) ?? collect();

            $ticketRevenue = 0;
            $comboRevenue = 0;

            foreach ($dayBookings as $b) {
                $ticketRevenue += $b->bookingDetails->sum('price');
                $comboRevenue += $b->combos->sum(function ($c) {
                    return $c->pivot->subtotal;
                });
            }

            $ticketData[] = $ticketRevenue;
            $comboData[] = $comboRevenue;

            $current->addDay();
        }

        return response()->json([
            'labels' => $labels,
            'ticket_revenue' => $ticketData,
            'combo_revenue' => $comboData,
        ]);
    }

    /**
     * 2.2 API Biểu đồ Top 5 Phim (charts/top-movies)
     * Lấy top 5 phim có số lượng vé bán ra cao nhất trong khoảng start_date và end_date.
     * Mặc định là 30 ngày qua.
     */
    public function chartsTopMovies(Request $request)
    {
        $startDateInput = $request->query('start_date');
        $endDateInput = $request->query('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Mặc định là 30 ngày gần đây
            $startDate = now()->subDays(29)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Tổng số vé của tất cả các phim trong kỳ
        $totalTicketsCount = BookingDetail::whereHas('booking', function ($q) use ($startDate, $endDate) {
            $q->where('payment_status', 'paid')
              ->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Top 5 phim bán chạy nhất trong kỳ
        $topMovies = Movie::join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
            ->join('bookings', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('booking_details', 'bookings.id', '=', 'booking_details.booking_id')
            ->where('bookings.payment_status', 'paid')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select('movies.id', 'movies.title', DB::raw('COUNT(booking_details.id) as tickets_count'))
            ->groupBy('movies.id', 'movies.title')
            ->orderByDesc('tickets_count')
            ->limit(5)
            ->get();

        $result = $topMovies->map(function ($movie) use ($totalTicketsCount) {
            $percentage = $totalTicketsCount > 0 ? round(($movie->tickets_count / $totalTicketsCount) * 100, 2) : 0;
            return [
                'title' => $movie->title,
                'tickets_count' => (int) $movie->tickets_count,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'total_tickets_in_period' => $totalTicketsCount,
            'top_movies' => $result
        ]);
    }

    /**
     * 3.1 API Vận hành Real-time Lỗi thanh toán (ops/conflicts)
     * Lấy danh sách 10 giao dịch lỗi thanh toán muộn trạng thái pending để kế toán xử lý.
     */
    public function opsConflicts(Request $request)
    {
        $conflicts = ConflictPayment::with('booking.user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'booking_id' => $c->booking_id,
                    'booking_code' => $c->booking_code,
                    'transaction_code' => $c->transaction_code,
                    'amount' => $c->amount,
                    'payment_method' => $c->payment_method,
                    'reason' => $c->reason,
                    'status' => $c->status,
                    'customer_name' => $c->booking?->user?->full_name ?? 'Khách vãng lai',
                    'customer_email' => $c->booking?->user?->email ?? 'N/A',
                    'created_at' => $c->created_at->format('H:i d/m/Y'),
                ];
            });

        return response()->json($conflicts);
    }

    /**
     * 3.2 API Vận hành Real-time Suất chiếu hôm nay (ops/today-showtimes)
     * Lấy các suất chiếu trong ngày (start_time >= now), kèm tỷ lệ ghế đã bán.
     * Sử dụng withCount để tối ưu truy vấn đếm quan hệ, loại bỏ N+1 query.
     */
    public function opsTodayShowtimes(Request $request)
    {
        $today = today()->format('Y-m-d');
        $nowTime = now()->format('H:i:s');

        $showtimes = Showtime::with(['movie', 'room'])
            ->withCount([
                'showtimeSeats as total_seats',
                'showtimeSeats as booked_seats' => function ($query) {
                    $query->where('status', 'booked');
                }
            ])
            ->whereDate('show_date', $today)
            ->where('start_time', '>=', $nowTime)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time', 'asc')
            ->get();

        $result = $showtimes->map(function ($showtime) {
            return [
                'id' => $showtime->id,
                'movie_title' => $showtime->movie->title,
                'room_name' => $showtime->room->room_name,
                'start_time' => Carbon::parse($showtime->start_time)->format('H:i'),
                'end_time' => $showtime->end_time ? Carbon::parse($showtime->end_time)->format('H:i') : null,
                'booked_seats' => $showtime->booked_seats,
                'total_seats' => $showtime->total_seats,
                'occupancy_percentage' => $showtime->total_seats > 0 
                    ? round(($showtime->booked_seats / $showtime->total_seats) * 100, 2) 
                    : 0,
            ];
        });

        return response()->json($result);
    }

    /**
     * Helper tính tỷ lệ tăng trưởng
     */
    private function calculateGrowth($today, $yesterday)
    {
        if ($yesterday == 0) {
            return $today > 0 ? 100 : 0;
        }
        return round((($today - $yesterday) / $yesterday) * 100, 2);
    }

    /**
     * 3.3. API Resolve Lỗi thanh toán (ops/conflicts/{id}/resolve)
     * Cập nhật trạng thái của giao dịch lỗi thanh toán muộn thành 'resolved'.
     */
    public function resolveConflict(Request $request, $id)
    {
        $conflict = ConflictPayment::findOrFail($id);
        $conflict->update(['status' => 'resolved']);

        return response()->json([
            'success' => true,
            'message' => 'Đã giải quyết giao dịch lỗi thanh toán muộn thành công.'
        ]);
    }
}

