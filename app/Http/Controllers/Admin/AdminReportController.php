<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        // Doanh thu vé: bookings paid có showtime → room → cinema
        $ticketQuery = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->join('cinemas', 'rooms.cinema_id', '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('cinemas.deleted_at')
            ->whereNull('rooms.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select(
                'cinemas.id as cinema_id',
                DB::raw('COUNT(DISTINCT bookings.id) as ticket_orders'),
                DB::raw('SUM(bookings.subtotal) as ticket_revenue')
            )
            ->groupBy('cinemas.id');

        if ($from) $ticketQuery->whereDate('bookings.created_at', '>=', $from);
        if ($to)   $ticketQuery->whereDate('bookings.created_at', '<=', $to);

        $ticketStats = $ticketQuery->get()->keyBy('cinema_id');

        // Doanh thu combo gắn kèm vé (booking_combos) theo cinema
        $comboTicketQuery = DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->join('cinemas', 'rooms.cinema_id', '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('cinemas.deleted_at')
            ->whereNull('rooms.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('cinemas.id as cinema_id', DB::raw('SUM(booking_combos.subtotal) as fnb_from_ticket'))
            ->groupBy('cinemas.id');

        if ($from) $comboTicketQuery->whereDate('bookings.created_at', '>=', $from);
        if ($to)   $comboTicketQuery->whereDate('bookings.created_at', '<=', $to);

        $comboTicketStats = $comboTicketQuery->get()->keyBy('cinema_id');

        // Đếm số vé (booking_details) theo cinema
        $seatQuery = DB::table('booking_details')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->join('cinemas', 'rooms.cinema_id', '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->whereNull('cinemas.deleted_at')
            ->whereNull('rooms.deleted_at')
            ->whereNull('showtimes.deleted_at')
            ->select('cinemas.id as cinema_id', DB::raw('COUNT(booking_details.id) as seat_count'))
            ->groupBy('cinemas.id');

        if ($from) $seatQuery->whereDate('bookings.created_at', '>=', $from);
        if ($to)   $seatQuery->whereDate('bookings.created_at', '<=', $to);

        $seatStats = $seatQuery->get()->keyBy('cinema_id');

        // Doanh thu F&B combo_only (không có showtime) — gắn theo staff → cinema qua user_cinemas
        $fnbQuery = DB::table('bookings')
            ->join('booking_combo_items', 'bookings.id', '=', 'booking_combo_items.booking_id')
            ->join('user_cinemas', 'bookings.staff_id', '=', 'user_cinemas.user_id')
            ->join('cinemas', 'user_cinemas.cinema_id', '=', 'cinemas.id')
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'combo_only')
            ->whereNull('cinemas.deleted_at')
            ->select('cinemas.id as cinema_id', DB::raw('SUM(booking_combo_items.subtotal) as fnb_only_revenue'))
            ->groupBy('cinemas.id');

        if ($from) $fnbQuery->whereDate('bookings.created_at', '>=', $from);
        if ($to)   $fnbQuery->whereDate('bookings.created_at', '<=', $to);

        $fnbStats = $fnbQuery->get()->keyBy('cinema_id');

        $cinemas = Cinema::orderBy('name')->get()->map(function ($cinema) use ($ticketStats, $seatStats, $fnbStats, $comboTicketStats) {
            $t   = $ticketStats->get($cinema->id);
            $s   = $seatStats->get($cinema->id);
            $f   = $fnbStats->get($cinema->id);
            $ct  = $comboTicketStats->get($cinema->id);

            $ticketRevenue = $t  ? (int) $t->ticket_revenue      : 0;
            $fnbRevenue    = ($ct ? (int) $ct->fnb_from_ticket    : 0)
                           + ($f  ? (int) $f->fnb_only_revenue    : 0);

            return [
                'name'           => $cinema->name,
                'address'        => $cinema->address,
                'ticket_count'   => $s ? (int) $s->seat_count : 0,
                'ticket_revenue' => $ticketRevenue,
                'fnb_revenue'    => $fnbRevenue,
                'total_revenue'  => $ticketRevenue + $fnbRevenue,
            ];
        });

        $summary = [
            'ticket_count'   => $cinemas->sum('ticket_count'),
            'ticket_revenue' => $cinemas->sum('ticket_revenue'),
            'fnb_revenue'    => $cinemas->sum('fnb_revenue'),
            'total_revenue'  => $cinemas->sum('total_revenue'),
        ];

        return view('admin.reports.index', compact('cinemas', 'summary', 'from', 'to'));
    }
}
