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
        $cinemas = $this->getAllowedCinemas();
        $cinemaIds = $cinemas->pluck('id')->toArray();

        // ── Doanh thu vé + số vé bán theo rạp ──────────────────────────────
        // bookings (ticket, paid) → showtimes → rooms → cinemas
        $ticketStats = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->whereIn('rooms.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'ticket')
            ->groupBy('rooms.cinema_id')
            ->select(
                'rooms.cinema_id',
                DB::raw('COUNT(bookings.id) as ticket_count'),
                DB::raw('SUM(bookings.final_total) as ticket_revenue')
            )
            ->get()
            ->keyBy('cinema_id');

        // ── Doanh thu F&B (combo gói) theo rạp ─────────────────────────────
        // booking_combos → bookings (paid) → showtimes → rooms → cinemas
        $fnbComboStats = DB::table('booking_combos')
            ->join('bookings', 'booking_combos.booking_id', '=', 'bookings.id')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->whereIn('rooms.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid')
            ->groupBy('rooms.cinema_id')
            ->select('rooms.cinema_id', DB::raw('SUM(booking_combos.subtotal) as fnb_revenue'))
            ->get()
            ->keyBy('cinema_id');

        // ── Doanh thu F&B (combo_only — mua lẻ tại quầy) theo rạp ──────────
        // bookings (combo_only, paid) → staff → user_cinemas → cinemas
        $fnbOnlyStats = DB::table('bookings')
            ->join('user_cinemas', 'bookings.staff_id', '=', 'user_cinemas.user_id')
            ->whereIn('user_cinemas.cinema_id', $cinemaIds)
            ->where('bookings.payment_status', 'paid')
            ->where('bookings.booking_type', 'combo_only')
            ->groupBy('user_cinemas.cinema_id')
            ->select('user_cinemas.cinema_id', DB::raw('SUM(bookings.final_total) as fnb_only_revenue'))
            ->get()
            ->keyBy('cinema_id');

        // ── Gắn số liệu vào từng rạp ────────────────────────────────────────
        $cinemas->each(function ($cinema) use ($ticketStats, $fnbComboStats, $fnbOnlyStats) {
            $cinema->ticket_count   = $ticketStats[$cinema->id]->ticket_count   ?? 0;
            $cinema->ticket_revenue = $ticketStats[$cinema->id]->ticket_revenue ?? 0;
            $cinema->fnb_revenue    = ($fnbComboStats[$cinema->id]->fnb_revenue ?? 0)
                                    + ($fnbOnlyStats[$cinema->id]->fnb_only_revenue ?? 0);
            $cinema->total_revenue  = $cinema->ticket_revenue + $cinema->fnb_revenue;
        });

        return view('manager.reports.index', compact('cinemas'));
    }
}
