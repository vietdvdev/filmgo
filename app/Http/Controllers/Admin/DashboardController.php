<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Showtime;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /**
     * Inject DashboardService để xử lý toàn bộ logic nghiệp vụ tại tầng Service.
     * Controller chỉ đảm nhận việc nhận request, gọi service, trả về response.
     */
    public function __construct(protected DashboardService $dashboardService) {}

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
        $endDateInput   = $request->query('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate   = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Mặc định là ngày hôm nay
            $startDate = today()->startOfDay();
            $endDate   = today()->endOfDay();
        }

        // Độ dài khoảng thời gian hiện tại để lùi lại khoảng thời gian so sánh tương đương
        $diffInDays    = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();
        $prevEndDate   = $startDate->copy()->subDay()->endOfDay();

        // Cache key động theo khoảng thời gian — TTL 5 phút
        $cacheKey = 'admin_dashboard_kpis_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $prevStartDate, $prevEndDate) {
            // Ủy quyền toàn bộ logic tính toán cho DashboardService
            return $this->dashboardService->getKpiData($startDate, $endDate, $prevStartDate, $prevEndDate);
        });

        return response()->json($data);
    }

    /**
     * 2.1 API Biểu đồ Doanh thu (charts/revenue)
     * Trả về doanh thu vé và combo nhóm theo từng ngày từ start_date đến end_date.
     * Mặc định là 7 ngày qua.
     *
     * Tối ưu: Dùng SQL GROUP BY DATE aggregate thay vì load tất cả booking vào memory.
     */
    public function chartsRevenue(Request $request)
    {
        $startDateInput = $request->query('start_date');
        $endDateInput   = $request->query('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate   = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Mặc định là 7 ngày gần đây
            $startDate = now()->subDays(6)->startOfDay();
            $endDate   = now()->endOfDay();
        }

        // Cache theo khoảng ngày — TTL 5 phút
        $cacheKey = 'admin_charts_revenue_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $result = $this->dashboardService->getRevenueChartData($startDate, $endDate);

            return [
                'labels'         => $result['labels'],
                'ticket_revenue' => $result['ticketData'],
                'combo_revenue'  => $result['comboData'],
            ];
        });

        return response()->json($data);
    }

    /**
     * 2.2 API Biểu đồ Top 5 Phim (charts/top-movies)
     * Lấy top 5 phim có số lượng vé bán ra cao nhất trong khoảng start_date và end_date.
     * Mặc định là 30 ngày qua.
     */
    public function chartsTopMovies(Request $request)
    {
        $startDateInput = $request->query('start_date');
        $endDateInput   = $request->query('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate   = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Mặc định là 30 ngày gần đây
            $startDate = now()->subDays(29)->startOfDay();
            $endDate   = now()->endOfDay();
        }

        // Cache theo khoảng ngày — TTL 5 phút
        $cacheKey = 'admin_charts_top_movies_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            // Tổng số vé của tất cả các phim trong kỳ — dùng JOIN thay vì whereHas
            $totalTicketsCount = \Illuminate\Support\Facades\DB::table('booking_details')
                ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
                ->where('bookings.payment_status', 'paid')
                ->whereBetween('bookings.created_at', [$startDate, $endDate])
                ->count();

            // Top 5 phim bán chạy nhất trong kỳ
            $topMovies = Movie::join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
                ->join('bookings', 'showtimes.id', '=', 'bookings.showtime_id')
                ->join('booking_details', 'bookings.id', '=', 'booking_details.booking_id')
                ->where('bookings.payment_status', 'paid')
                ->whereBetween('bookings.created_at', [$startDate, $endDate])
                ->select('movies.id', 'movies.title', \Illuminate\Support\Facades\DB::raw('COUNT(booking_details.id) as tickets_count'))
                ->groupBy('movies.id', 'movies.title')
                ->orderByDesc('tickets_count')
                ->limit(5)
                ->get();

            $result = $topMovies->map(function ($movie) use ($totalTicketsCount) {
                $percentage = $totalTicketsCount > 0
                    ? round(($movie->tickets_count / $totalTicketsCount) * 100, 2)
                    : 0;

                return [
                    'title'         => $movie->title,
                    'tickets_count' => (int) $movie->tickets_count,
                    'percentage'    => $percentage,
                ];
            });

            return [
                'total_tickets_in_period' => $totalTicketsCount,
                'top_movies'              => $result,
            ];
        });

        return response()->json($data);
    }

    /**
     * 3.2 API Vận hành Real-time Suất chiếu hôm nay (ops/today-showtimes)
     * Lấy các suất chiếu trong ngày (start_time >= now), kèm tỷ lệ ghế đã bán.
     * Sử dụng withCount để tối ưu truy vấn đếm quan hệ, loại bỏ N+1 query.
     */
    public function opsTodayShowtimes(Request $request)
    {
        $today   = today()->format('Y-m-d');
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
                'id'                   => $showtime->id,
                'movie_title'          => $showtime->movie->title,
                'room_name'            => $showtime->room->room_name,
                'start_time'           => Carbon::parse($showtime->start_time)->format('H:i'),
                'end_time'             => $showtime->end_time ? Carbon::parse($showtime->end_time)->format('H:i') : null,
                'booked_seats'         => $showtime->booked_seats,
                'total_seats'          => $showtime->total_seats,
                'occupancy_percentage' => $showtime->total_seats > 0
                    ? round(($showtime->booked_seats / $showtime->total_seats) * 100, 2)
                    : 0,
            ];
        });

        return response()->json($result);
    }
}
