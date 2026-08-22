<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function cinema(Request $request)
    {
        $cinemaId = $request->input('cinema_id');
        $day      = $request->input('day');
        $month    = $request->input('month');
        $year     = $request->input('year');
        $from     = $request->input('from');
        $to       = $request->input('to');

        $allCinemas = Cinema::orderBy('name')->get();

        // ── Closure áp filter ngày chung cho mọi query ──────────────────
        $applyDateFilter = function ($q) use ($cinemaId, $day, $month, $year, $from, $to) {
            if ($cinemaId) $q->where('cinemas.id', $cinemaId);
            if ($day)      $q->whereDay('bookings.created_at', $day);
            if ($month)    $q->whereMonth('bookings.created_at', $month);
            if ($year)     $q->whereYear('bookings.created_at', $year);
            if ($from)     $q->whereDate('bookings.created_at', '>=', $from);
            if ($to)       $q->whereDate('bookings.created_at', '<=', $to);
            return $q;
        };

        // ── Query 1: Doanh thu vé + đếm đơn ────────────────────────────
        $ticketQ = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms',     'showtimes.room_id',    '=', 'rooms.id')
            ->join('cinemas',   'rooms.cinema_id',      '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('cinemas.deleted_at')
            ->whereNull('rooms.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('cinemas.id as cinema_id',
                DB::raw('SUM(bookings.subtotal) as ticket_revenue'));
        $applyDateFilter($ticketQ);
        $ticketStats = $ticketQ->groupBy('cinemas.id')->get()->keyBy('cinema_id');

        // ── Query 2: Đếm số ghế/vé ──────────────────────────────────────
        $seatQ = DB::table('booking_details')
            ->join('bookings',  'booking_details.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id',       '=', 'showtimes.id')
            ->join('rooms',     'showtimes.room_id',          '=', 'rooms.id')
            ->join('cinemas',   'rooms.cinema_id',            '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('cinemas.deleted_at')
            ->whereNull('rooms.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('cinemas.id as cinema_id',
                DB::raw('COUNT(booking_details.id) as seat_count'));
        $applyDateFilter($seatQ);
        $seatStats = $seatQ->groupBy('cinemas.id')->get()->keyBy('cinema_id');

        // ── Query 3: F&B combo gắn kèm vé ──────────────────────────────
        $comboQ = DB::table('booking_combos')
            ->join('bookings',  'booking_combos.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id',      '=', 'showtimes.id')
            ->join('rooms',     'showtimes.room_id',         '=', 'rooms.id')
            ->join('cinemas',   'rooms.cinema_id',           '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('cinemas.deleted_at')
            ->whereNull('rooms.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('cinemas.id as cinema_id',
                DB::raw('SUM(booking_combos.subtotal) as fnb_ticket'));
        $applyDateFilter($comboQ);
        $comboStats = $comboQ->groupBy('cinemas.id')->get()->keyBy('cinema_id');

        // ── Query 4: F&B combo_only (bán lẻ tại quầy) ──────────────────
        $fnbQ = DB::table('bookings')
            ->join('booking_combo_items', 'bookings.id',          '=', 'booking_combo_items.booking_id')
            ->join('user_cinemas',        'bookings.staff_id',    '=', 'user_cinemas.user_id')
            ->join('cinemas',             'user_cinemas.cinema_id','=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type',   'combo_only')
            ->whereNull('cinemas.deleted_at')
            ->select('cinemas.id as cinema_id',
                DB::raw('SUM(booking_combo_items.subtotal) as fnb_only'));
        $applyDateFilter($fnbQ);
        $fnbStats = $fnbQ->groupBy('cinemas.id')->get()->keyBy('cinema_id');

        // ── Gộp kết quả ─────────────────────────────────────────────────
        $cinemas = $allCinemas->map(function ($c) use ($ticketStats, $seatStats, $comboStats, $fnbStats) {
            $ticketRevenue = (int) ($ticketStats[$c->id]->ticket_revenue ?? 0);
            $fnbRevenue    = (int) ($comboStats[$c->id]->fnb_ticket      ?? 0)
                           + (int) ($fnbStats[$c->id]->fnb_only          ?? 0);
            return [
                'id'             => $c->id,
                'name'           => $c->name,
                'address'        => $c->address,
                'ticket_count'   => (int) ($seatStats[$c->id]->seat_count ?? 0),
                'ticket_revenue' => $ticketRevenue,
                'fnb_revenue'    => $fnbRevenue,
                'total_revenue'  => $ticketRevenue + $fnbRevenue,
            ];
        });

        // Nếu lọc theo rạp cụ thể thì chỉ giữ rạp đó
        if ($cinemaId) {
            $cinemas = $cinemas->where('id', (int) $cinemaId)->values();
        }

        $summary = [
            'ticket_count'   => $cinemas->sum('ticket_count'),
            'ticket_revenue' => $cinemas->sum('ticket_revenue'),
            'fnb_revenue'    => $cinemas->sum('fnb_revenue'),
            'total_revenue'  => $cinemas->sum('total_revenue'),
        ];

        return view('admin.reports.cinema', compact(
            'cinemas', 'summary', 'allCinemas',
            'cinemaId', 'day', 'month', 'year', 'from', 'to'
        ));
    }

    public function movie(Request $request)
    {
        $movieId  = $request->input('movie_id');
        $day      = $request->input('day');
        $month    = $request->input('month');
        $year     = $request->input('year');
        $from     = $request->input('from');
        $to       = $request->input('to');

        $allMovies = \App\Models\Movie::orderBy('title')->get();

        $applyDateFilter = function ($q) use ($movieId, $day, $month, $year, $from, $to) {
            if ($movieId) $q->where('movies.id', $movieId);
            if ($day)      $q->whereDay('bookings.created_at', $day);
            if ($month)    $q->whereMonth('bookings.created_at', $month);
            if ($year)     $q->whereYear('bookings.created_at', $year);
            if ($from)     $q->whereDate('bookings.created_at', '>=', $from);
            if ($to)       $q->whereDate('bookings.created_at', '<=', $to);
            return $q;
        };

        // Query 1: Doanh thu vé
        $ticketQ = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('movies.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('movies.id as movie_id', DB::raw('SUM(bookings.subtotal) as ticket_revenue'));
        $applyDateFilter($ticketQ);
        $ticketStats = $ticketQ->groupBy('movies.id')->get()->keyBy('movie_id');

        // Query 2: Đếm vé (ghế)
        $seatQ = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('movies.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('movies.id as movie_id', DB::raw('COUNT(booking_details.id) as seat_count'));
        $applyDateFilter($seatQ);
        $seatStats = $seatQ->groupBy('movies.id')->get()->keyBy('movie_id');

        // Query 3: F&B mua kèm
        $comboQ = DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('movies.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('movies.id as movie_id', DB::raw('SUM(booking_combos.subtotal) as fnb_ticket'));
        $applyDateFilter($comboQ);
        $comboStats = $comboQ->groupBy('movies.id')->get()->keyBy('movie_id');

        $moviesList = $allMovies->map(function ($m) use ($ticketStats, $seatStats, $comboStats) {
            $ticketRevenue = (int) ($ticketStats[$m->id]->ticket_revenue ?? 0);
            $fnbRevenue    = (int) ($comboStats[$m->id]->fnb_ticket ?? 0);
            return [
                'id'             => $m->id,
                'title'          => $m->title,
                'ticket_count'   => (int) ($seatStats[$m->id]->seat_count ?? 0),
                'ticket_revenue' => $ticketRevenue,
                'fnb_revenue'    => $fnbRevenue,
                'total_revenue'  => $ticketRevenue + $fnbRevenue,
            ];
        });

        if ($movieId) {
            $moviesList = $moviesList->where('id', (int) $movieId)->values();
        }

        $moviesList = $moviesList->sortByDesc('total_revenue')->values();

        $summary = [
            'ticket_count'   => $moviesList->sum('ticket_count'),
            'ticket_revenue' => $moviesList->sum('ticket_revenue'),
            'fnb_revenue'    => $moviesList->sum('fnb_revenue'),
            'total_revenue'  => $moviesList->sum('total_revenue'),
        ];

        return view('admin.reports.movie', compact(
            'moviesList', 'summary', 'allMovies',
            'movieId', 'day', 'month', 'year', 'from', 'to'
        ));
    }
}
