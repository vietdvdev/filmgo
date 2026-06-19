<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $cinema = (object)[
            'name' => 'BHD Star GigaMall Đà Nẵng'
        ];

        // Mock dữ liệu giả lập cho dashboard quản trị rạp
        $roomCount = 6;
        $staffCount = 12;
        $showtimeTodayCount = 24;

        return view('manager.dashboard', compact('cinema', 'roomCount', 'staffCount', 'showtimeTodayCount'));
    }
}
