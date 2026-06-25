<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerReportController extends Controller
{
    private function getCinemaId()
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'admin')->exists()) {
            $cinema = $user->cinemas()->first() ?? Cinema::first();
        } else {
            $cinema = $user->cinemas()->first();
        }

        if (!$cinema) {
            abort(403, 'Tài khoản chưa được phân công quản lý rạp nào.');
        }

        return $cinema->id;
    }

    public function index(Request $request)
    {
        // Sử dụng dữ liệu giả lập (mock data) để hiển thị giao diện báo cáo chưa cần dữ liệu thật
        $revenueToday = 1850000;
        $revenueWeek = 12900000;
        
        $totalSeats = 450;
        $bookedSeats = 288;
        $occupancyRate = 64.0;

        $movieRevenues = collect([
            (object)[
                'title' => 'Lật Mặt 7: Một Điều Ước',
                'total_bookings' => 120,
                'total_revenue' => 9600000
            ],
            (object)[
                'title' => 'Doraemon: Bản Giao Hưởng Địa Cầu',
                'total_bookings' => 98,
                'total_revenue' => 7840000
            ],
            (object)[
                'title' => 'Haikyu!!: Trận Chiến Bãi Phế Thải',
                'total_bookings' => 70,
                'total_revenue' => 5600000
            ]
        ]);

        return view('manager.reports.index', compact('revenueToday', 'revenueWeek', 'occupancyRate', 'movieRevenues', 'totalSeats', 'bookedSeats'));
    }
}
