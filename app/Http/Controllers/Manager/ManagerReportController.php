<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerReportController extends Controller
{
    private function getAllowedCinemas()
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'admin')->exists()) {
            return Cinema::withCount('rooms')->get();
        }
        return $user->cinemas()->withCount('rooms')->get();
    }

    public function index(Request $request)
    {
        $allCinemas  = $this->getAllowedCinemas();
        $allCinemaIds = $allCinemas->pluck('id')->toArray();

        // ── Bộ lọc ──────────────────────────────────────────────────────────
        $filterCinemaId = $request->input('cinema_id');          // null = tất cả
        $filterType     = $request->input('filter_type', 'all'); // all | day | month | year
        $filterDate     = $request->input('filter_date');        // Y-m-d
        $filterMonth    = $request->input('filter_month');       // Y-m
        $filterYear     = $request->input('filter_year');        // Y

        // Tập cinema_id thực sự dùng để query
        $cinemaIds = ($filterCinemaId && in_array($filterCinemaId, $allCinemaIds))
            ? [(int) $filterCinemaId]
            : $allCinemaIds;

        // Cinemas hiển thị (đã lọc theo rạp nếu có)
        $cinemas = $allCinemas->when($filterCinemaId, fn($c) => $c->where('id', (int) $filterCinemaId));

        // ── Helper: áp bộ lọc thời gian vào query ───────────────────────────
        $applyDateFilter = function ($query, string $dateColumn) use ($filterType, $filterDate, $filterMonth, $filterYear) {
            match ($filterType) {
                'day'   => $query->whereDate($dateColumn, $filterDate),
                'month' => $query->whereYear($dateColumn, substr($filterMonth, 0, 4))
                                 ->whereMonth($dateColumn, substr($filterMonth, 5, 2)),
                'year'  => $query->whereYear($dateColumn, $filterYear),
                default => null,
            };
        };

        // ── Doanh thu vé ────────────────────────────────────────────────────
        $ticketQuery = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->whereIn('rooms.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'ticket');
        $applyDateFilter($ticketQuery, 'bookings.created_at');
        $ticketStats = $ticketQuery
            ->groupBy('rooms.cinema_id')
            ->select(
                'rooms.cinema_id',
                DB::raw('COUNT(bookings.id) as ticket_count'),
                DB::raw('SUM(bookings.final_total) as ticket_revenue')
            )
            ->get()->keyBy('cinema_id');

        // ── Doanh thu F&B (combo gói kèm vé) ────────────────────────────────
        $fnbComboQuery = DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->whereIn('rooms.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid');
        $applyDateFilter($fnbComboQuery, 'bookings.created_at');
        $fnbComboStats = $fnbComboQuery
            ->groupBy('rooms.cinema_id')
            ->select('rooms.cinema_id', DB::raw('SUM(booking_combos.subtotal) as fnb_revenue'))
            ->get()->keyBy('cinema_id');

        // ── Doanh thu F&B (combo_only — mua lẻ tại quầy) ───────────────────
        $fnbOnlyQuery = DB::table('bookings')
            ->join('user_cinemas', 'bookings.staff_id', '=', 'user_cinemas.user_id')
            ->whereIn('user_cinemas.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'combo_only');
        $applyDateFilter($fnbOnlyQuery, 'bookings.created_at');
        $fnbOnlyStats = $fnbOnlyQuery
            ->groupBy('user_cinemas.cinema_id')
            ->select('user_cinemas.cinema_id', DB::raw('SUM(bookings.final_total) as fnb_only_revenue'))
            ->get()->keyBy('cinema_id');

        // ── Gắn số liệu vào từng rạp ────────────────────────────────────────
        $cinemas->each(function ($cinema) use ($ticketStats, $fnbComboStats, $fnbOnlyStats) {
            $cinema->ticket_count   = $ticketStats[$cinema->id]->ticket_count   ?? 0;
            $cinema->ticket_revenue = $ticketStats[$cinema->id]->ticket_revenue ?? 0;
            $cinema->fnb_revenue    = ($fnbComboStats[$cinema->id]->fnb_revenue    ?? 0)
                                    + ($fnbOnlyStats[$cinema->id]->fnb_only_revenue ?? 0);
            $cinema->total_revenue  = $cinema->ticket_revenue + $cinema->fnb_revenue;
        });

        // ── Tổng hợp toàn bộ (sau lọc) ──────────────────────────────────────
        $summary = [
            'ticket_count'   => $cinemas->sum('ticket_count'),
            'ticket_revenue' => $cinemas->sum('ticket_revenue'),
            'fnb_revenue'    => $cinemas->sum('fnb_revenue'),
            'total_revenue'  => $cinemas->sum('total_revenue'),
        ];

        // ── Doanh thu theo phim (chỉ khi chọn 1 rạp cụ thể) ───────────────
        $movieStats = collect();
        if ($filterCinemaId && in_array((int) $filterCinemaId, $allCinemaIds)) {
            $cid = (int) $filterCinemaId;

            // Vé + số suất chiếu
            $movieTicketQuery = DB::table('bookings')
                ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
                ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
                ->where('rooms.cinema_id', $cid)
                ->where('bookings.payment_status', 'paid')
                ->where('bookings.booking_type', 'ticket');
            $applyDateFilter($movieTicketQuery, 'bookings.created_at');
            $movieTickets = $movieTicketQuery
                ->groupBy('movies.id', 'movies.title')
                ->select(
                    'movies.id',
                    'movies.title',
                    DB::raw('COUNT(DISTINCT showtimes.id) as showtime_count'),
                    DB::raw('COUNT(bookings.id) as ticket_count'),
                    DB::raw('SUM(bookings.final_total) as ticket_revenue')
                )
                ->get()->keyBy('id');

            // F&B kèm vé theo phim
            $movieFnbQuery = DB::table('booking_combos')
                ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
                ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
                ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
                ->where('rooms.cinema_id', $cid)
                ->where('bookings.payment_status', 'paid');
            $applyDateFilter($movieFnbQuery, 'bookings.created_at');
            $movieFnb = $movieFnbQuery
                ->groupBy('movies.id')
                ->select('movies.id', DB::raw('SUM(booking_combos.subtotal) as fnb_revenue'))
                ->get()->keyBy('id');

            $movieStats = $movieTickets->map(function ($m) use ($movieFnb) {
                $m->fnb_revenue   = $movieFnb[$m->id]->fnb_revenue ?? 0;
                $m->total_revenue = $m->ticket_revenue + $m->fnb_revenue;
                return $m;
            })->sortByDesc('total_revenue')->values();
        }

        return view('manager.reports.index', compact(
            'cinemas', 'allCinemas', 'summary',
            'filterCinemaId', 'filterType', 'filterDate', 'filterMonth', 'filterYear',
            'movieStats'
        ));
    }
}
