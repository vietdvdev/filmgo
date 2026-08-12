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
        $allCinemas   = $this->getAllowedCinemas();
        $allCinemaIds = $allCinemas->pluck('id')->toArray();

        $filterCinemaId = $request->input('cinema_id');
        $filterMovieId  = $request->input('movie_id');
        $filterType     = $request->input('filter_type', 'all');
        $filterDate     = $request->input('filter_date');
        $filterMonth    = $request->input('filter_month');
        $filterYear     = $request->input('filter_year');
        $sortBy         = in_array($request->input('sort'), ['total_revenue', 'ticket_count', 'showtime_count', 'ticket_revenue'])
                          ? $request->input('sort') : 'total_revenue';

        $cinemaIds = ($filterCinemaId && in_array((int) $filterCinemaId, $allCinemaIds))
            ? [(int) $filterCinemaId]
            : $allCinemaIds;

        $cinemas = $allCinemas->when($filterCinemaId, fn($c) => $c->where('id', (int) $filterCinemaId));

        $applyDateFilter = function ($query, string $col) use ($filterType, $filterDate, $filterMonth, $filterYear) {
            match ($filterType) {
                'day'   => $query->whereDate($col, $filterDate),
                'month' => $query->whereYear($col, substr($filterMonth, 0, 4))
                                 ->whereMonth($col, substr($filterMonth, 5, 2)),
                'year'  => $query->whereYear($col, $filterYear),
                default => null,
            };
        };

        // Doanh thu vé theo rạp
        $ticketQuery = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->whereIn('rooms.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'ticket');
        $applyDateFilter($ticketQuery, 'bookings.created_at');
        $ticketStats = $ticketQuery
            ->groupBy('rooms.cinema_id')
            ->select('rooms.cinema_id', DB::raw('COUNT(bookings.id) as ticket_count'), DB::raw('SUM(bookings.final_total) as ticket_revenue'))
            ->get()->keyBy('cinema_id');

        // F&B kèm vé theo rạp
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

        // F&B combo_only theo rạp
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

        $cinemas->each(function ($cinema) use ($ticketStats, $fnbComboStats, $fnbOnlyStats) {
            $cinema->ticket_count   = $ticketStats[$cinema->id]->ticket_count   ?? 0;
            $cinema->ticket_revenue = $ticketStats[$cinema->id]->ticket_revenue ?? 0;
            $cinema->fnb_revenue    = ($fnbComboStats[$cinema->id]->fnb_revenue       ?? 0)
                                    + ($fnbOnlyStats[$cinema->id]->fnb_only_revenue   ?? 0);
            $cinema->total_revenue  = $cinema->ticket_revenue + $cinema->fnb_revenue;
        });

        $summary = [
            'ticket_count'   => $cinemas->sum('ticket_count'),
            'ticket_revenue' => $cinemas->sum('ticket_revenue'),
            'fnb_revenue'    => $cinemas->sum('fnb_revenue'),
            'total_revenue'  => $cinemas->sum('total_revenue'),
        ];

        // Doanh thu theo phim (chỉ khi chọn rạp)
        $movieStats   = collect();
        $cinemaMovies = collect();

        if ($filterCinemaId && in_array((int) $filterCinemaId, $allCinemaIds)) {
            $cid = (int) $filterCinemaId;

            // Danh sách phim của rạp để populate dropdown (không lọc thời gian)
            $cinemaMovies = DB::table('movies')
                ->join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
                ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                ->where('rooms.cinema_id', $cid)
                ->whereNull('movies.deleted_at')
                ->distinct()
                ->select('movies.id', 'movies.title')
                ->orderBy('movies.title')
                ->get();

            // Vé + suất chiếu theo phim
            $movieTicketQuery = DB::table('bookings')
                ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
                ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
                ->where('rooms.cinema_id', $cid)
                ->where('bookings.payment_status', 'paid')
                ->where('bookings.booking_type', 'ticket');
            $applyDateFilter($movieTicketQuery, 'bookings.created_at');
            if ($filterMovieId) {
                $movieTicketQuery->where('movies.id', (int) $filterMovieId);
            }
            $movieTickets = $movieTicketQuery
                ->groupBy('movies.id', 'movies.title')
                ->select(
                    'movies.id', 'movies.title',
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
            if ($filterMovieId) {
                $movieFnbQuery->where('movies.id', (int) $filterMovieId);
            }
            $movieFnb = $movieFnbQuery
                ->groupBy('movies.id')
                ->select('movies.id', DB::raw('SUM(booking_combos.subtotal) as fnb_revenue'))
                ->get()->keyBy('id');

            $movieStats = $movieTickets->map(function ($m) use ($movieFnb) {
                $m->fnb_revenue   = $movieFnb[$m->id]->fnb_revenue ?? 0;
                $m->total_revenue = $m->ticket_revenue + $m->fnb_revenue;
                return $m;
            })->sortByDesc($sortBy)->values();
        }

        return view('manager.reports.index', compact(
            'cinemas', 'allCinemas', 'summary',
            'filterCinemaId', 'filterMovieId', 'filterType', 'filterDate', 'filterMonth', 'filterYear',
            'sortBy', 'movieStats', 'cinemaMovies'
        ));
    }
}
