<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffShowtimeController extends Controller
{
    private function authorizeStaff(): void
    {
        $user = Auth::user();
        if (!$user->roles()->where('name', 'staff')->exists()) {
            abort(403, 'Chức năng này chỉ dành cho nhân viên (Staff).');
        }
    }

    /**
     * Lấy cinema_id mà staff đang được phân công.
     */
    private function getCinemaId(): int
    {
        $cinema = Auth::user()->cinemas()->first();
        if (!$cinema) {
            abort(403, 'Bạn chưa được phân công vào rạp nào. Vui lòng liên hệ quản lý.');
        }
        return $cinema->id;
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $cinemaId = $this->getCinemaId();

        // Lấy danh sách phòng thuộc rạp để render filter
        $rooms = Room::where('cinema_id', $cinemaId)
            ->orderBy('room_name')
            ->get(['id', 'room_name', 'room_type']);

        // Query suất chiếu hôm nay tại rạp
        $query = Showtime::with(['movie', 'room'])
            ->whereHas('room', fn($q) => $q->where('cinema_id', $cinemaId))
            ->whereDate('show_date', today())
            ->orderBy('start_time');

        // Lọc theo phòng chiếu
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->integer('room_id'));
        }

        // Tìm kiếm theo tên phim
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('movie', fn($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $showtimes = $query->get();

        return view('admin.staff.showtimes', compact('showtimes', 'rooms'));
    }
}
