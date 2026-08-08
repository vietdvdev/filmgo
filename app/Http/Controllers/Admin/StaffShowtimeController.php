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

    /**
     * Hiển thị danh sách suất chiếu hôm nay tại rạp của nhân viên.
     * Mặc định ẩn các suất chiếu đã kết thúc để tránh bán nhầm vé.
     */
    public function index(Request $request)
    {
        $this->authorizeStaff();

        $cinemaId = $this->getCinemaId();

        // Lấy danh sách phòng thuộc rạp để render filter
        $rooms = Room::where('cinema_id', $cinemaId)
            ->orderBy('room_name')
            ->get(['id', 'room_name', 'room_type']);

        $includeEnded = $request->boolean('include_ended');
        $currentTime  = now()->toTimeString();
        $todayDate    = today()->toDateString();

        // PART 1: QUERY OPTIMIZATION
        // Eager load 'movie' và 'room' để phòng tránh lỗi N+1 queries.
        // Filter theo cinema_id trực thuộc rạp của nhân viên đang đăng nhập.
        $query = Showtime::with(['movie', 'room'])
            ->whereHas('room', fn($q) => $q->where('cinema_id', $cinemaId))
            ->whereDate('show_date', '>=', $todayDate);

        // Logic lọc thời gian: Mặc định ẩn các suất chiếu đã xong hôm nay.
        // Suất chiếu hợp lệ nếu: (show_date > today) HOẶC (show_date == today VÀ end_time > current_time)
        if (!$includeEnded) {
            $query->where(function ($q) use ($todayDate, $currentTime) {
                $q->whereDate('show_date', '>', $todayDate)
                  ->orWhere(function ($q2) use ($todayDate, $currentTime) {
                      $q2->whereDate('show_date', '=', $todayDate)
                         ->where('end_time', '>', $currentTime);
                  });
            });
        }

        // Lọc theo phòng chiếu
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->integer('room_id'));
        }

        // Tìm kiếm theo tên phim
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('movie', fn($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $showtimes = $query->orderBy('show_date')->orderBy('start_time')->get();

        // Cập nhật trạng thái thực tế dựa theo thời gian hiện tại
        $now = now();
        $showtimes->each(function ($showtime) use ($now) {
            if ($showtime->status === 'cancelled') return;

            $start = \Carbon\Carbon::parse($showtime->show_date->toDateString() . ' ' . $showtime->start_time);
            $end   = \Carbon\Carbon::parse($showtime->show_date->toDateString() . ' ' . $showtime->end_time);

            if ($now->gt($end)) {
                $showtime->status = 'finished';
                // BUG-05 FIX: Lưu trạng thái vào DB để các query khác (report, POS filter)
                // đọc được trạng thái đúng. Trước đây chỉ gán thuộc tính model mà không lưu.
                $showtime->saveQuietly();
            } elseif ($now->gte($start)) {
                $showtime->status = 'showing';
                $showtime->saveQuietly();
            }
        });

        return view('admin.staff.showtimes', compact('showtimes', 'rooms', 'includeEnded'));
    }
}
