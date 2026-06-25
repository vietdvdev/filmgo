<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerShowtimeController extends Controller
{
    private function getCinemaId(): int
    {
        $user = Auth::user();

        // Admin truy cập giao diện manager — lấy cinema được gán hoặc fallback về cinema đầu tiên
        if ($user->roles()->where('name', 'admin')->exists()) {
            $cinema = $user->cinemas()->first() ?? Cinema::first();
        } else {
            $cinema = $user->cinemas()->first();
        }

        if (!$cinema) {
            abort(403, 'Tài khoản của bạn chưa được phân công quản lý rạp nào. Vui lòng liên hệ Admin.');
        }

        return $cinema->id;
    }

    public function index(Request $request)
    {
        $cinemaId = $this->getCinemaId();
        $showDate = $request->filled('date') ? $request->date : today()->toDateString();

        $showtimes = Showtime::with(['movie', 'room'])
            ->whereHas('room', fn($q) => $q->where('cinema_id', $cinemaId))
            ->whereDate('show_date', $showDate)
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('manager.showtimes.index', compact('showtimes'));
    }

    public function create()
    {
        $cinemaId = $this->getCinemaId();
        $movies = Movie::where('status', 'showing')->orderBy('title')->get();
        $rooms  = Room::where('cinema_id', $cinemaId)->where('status', 'active')->orderBy('room_name')->get();

        return view('manager.showtimes.create', compact('movies', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'movie_id'   => 'required|exists:movies,id',
            'room_id'    => 'required|exists:rooms,id',
            'show_date'  => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'base_price' => 'required|numeric|min:0',
        ], [
            'movie_id.required'   => 'Vui lòng chọn phim.',
            'room_id.required'    => 'Vui lòng chọn phòng chiếu.',
            'show_date.required'  => 'Vui lòng chọn ngày chiếu.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'base_price.required' => 'Vui lòng nhập giá vé cơ bản.',
        ]);

        // Verify room belongs to manager's cinema
        $cinemaId = $this->getCinemaId();
        $room = Room::where('id', $request->room_id)->where('cinema_id', $cinemaId)->firstOrFail();

        $movie    = Movie::findOrFail($request->movie_id);
        $start    = Carbon::parse($request->show_date . ' ' . $request->start_time);
        $end      = $start->copy()->addMinutes($movie->duration ?? 120);

        Showtime::create([
            'movie_id'   => $request->movie_id,
            'room_id'    => $request->room_id,
            'show_date'  => $request->show_date,
            'start_time' => $start->format('H:i:s'),
            'end_time'   => $end->format('H:i:s'),
            'base_price' => $request->base_price,
            'status'     => 'upcoming',
        ]);

        return redirect()->route('manager.showtimes.index')->with('success', 'Tạo suất chiếu mới thành công!');
    }

    public function cancelShowtime($id)
    {
        $cinemaId = $this->getCinemaId();

        // Bước 1: Xác thực quyền — suất phải thuộc phòng trong rạp của manager
        $showtime = Showtime::whereHas(
            'room',
            fn($q) => $q->where('cinema_id', $cinemaId)
        )->findOrFail($id);

        if ($showtime->status === 'cancelled') {
            return back()->with('error', 'Suất chiếu này đã được hủy trước đó.');
        }

        // Bước 2: Pre-flight check — không được có ghế 'booked'
        $hasBookedSeat = ShowtimeSeat::where('showtime_id', $id)
            ->where('status', 'booked')
            ->exists();

        if ($hasBookedSeat) {
            return back()->with(
                'error',
                'Không thể hủy suất chiếu đã có khách mua vé. Vui lòng thực hiện quy trình hoàn tiền (Refund) trước.'
            );
        }

        // Bước 3 & 4: Xóa ghế + cập nhật trạng thái trong Transaction
        DB::transaction(function () use ($showtime) {
            // Bước 3: Hard delete toàn bộ showtime_seats (kể cả ghế 'holding' hết hạn)
            ShowtimeSeat::where('showtime_id', $showtime->id)->delete();

            // Bước 4: Soft delete + đánh dấu cancelled
            $showtime->update(['status' => 'cancelled']);
            $showtime->delete(); // sets deleted_at
        });

        // Bước 5: Ghi audit log
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'CANCEL_SHOWTIME',
            'model_type'  => 'Showtime',
            'model_id'    => $showtime->id,
            'description' => 'Manager đã hủy suất chiếu ID=' . $showtime->id,
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->route('manager.showtimes.index')
            ->with('success', 'Suất chiếu đã được hủy thành công.');
    }
}
