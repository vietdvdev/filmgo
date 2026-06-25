<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Models\ActivityLog;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ManagerShowtimeController extends Controller
{
    /**
     * Lấy danh sách ID các rạp mà user hiện tại được phân công.
     * Admin fallback về tất cả rạp.
     */
    private function getCinemaIds(): array
    {
        $user = Auth::user();

        if ($user->roles()->where('name', 'admin')->exists()) {
            return Cinema::pluck('id')->toArray();
        }

        $ids = $user->cinemas()->pluck('cinemas.id')->toArray();

        if (empty($ids)) {
            abort(403, 'Tài khoản của bạn chưa được phân công quản lý rạp nào. Vui lòng liên hệ Admin.');
        }

        return $ids;
    }

    public function index(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();

        $showDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))?->startOfDay() ?? Carbon::today()
            : Carbon::today();

        $showtimes = Showtime::whereIn('room_id', function ($query) use ($cinemaIds) {
                $query->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds)->whereNull('deleted_at');
            })
            ->whereDate('show_date', $showDate)
            ->with(['movie', 'room', 'room.cinema'])
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('manager.showtimes.index', compact('showtimes', 'showDate'));
    }

    public function create()
    {
        $movies = Movie::whereIn('status', ['showing', 'upcoming'])
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'age_limit']);

        return view('manager.showtimes.create', compact('movies'));
    }

    public function store(StoreShowtimeRequest $request)
    {
        // Bước 1: Xác thực quyền qua Policy
        $room = Room::findOrFail($request->integer('room_id'));

        if ($room->status !== 'active') {
            return back()->withInput()->withErrors(['room_id' => 'Phòng chiếu này hiện không hoạt động.']);
        }

        Gate::authorize('create', [Showtime::class, $room]);

        // Bước 2: Tính end_time
        $movie = Movie::findOrFail($request->integer('movie_id'));

        if ($movie->status === 'stopped') {
            return back()->withInput()->withErrors(['movie_id' => 'Phim này đã ngừng chiếu, không thể tạo suất chiếu mới.']);
        }

        $startTime   = Carbon::createFromFormat('Y-m-d H:i', $request->input('show_date') . ' ' . $request->input('start_time'));
        $endTime     = $startTime->copy()->addMinutes($movie->duration);
        $startStr    = $startTime->format('H:i:s');
        $endStr      = $endTime->format('H:i:s');
        $showDateStr = $request->input('show_date');

        // Bước 3: Kiểm tra trùng lịch
        $overlapQuery = Showtime::where('room_id', $room->id)->where('show_date', $showDateStr);

        if ($endStr <= $startStr) {
            // Phim chiếu qua nửa đêm
            $overlapQuery->where('start_time', '>=', $startStr);
        } else {
            $overlapQuery->where(function ($q) use ($startStr, $endStr) {
                $q->where('start_time', '<', $endStr)->where('end_time', '>', $startStr);
            });
        }

        $overlap = $overlapQuery->with('movie')->first();

        if ($overlap) {
            return back()->withInput()->withErrors([
                'start_time' => sprintf(
                    'Trùng lịch! Khung giờ %s–%s bị chồng lấn với suất chiếu "%s" (%s–%s) tại phòng này.',
                    $startTime->format('H:i'),
                    $endTime->format('H:i'),
                    $overlap->movie->title,
                    Carbon::parse($overlap->start_time)->format('H:i'),
                    Carbon::parse($overlap->end_time)->format('H:i')
                ),
            ]);
        }

        // Bước 4: Transaction — lưu suất chiếu + sinh sơ đồ ghế
        DB::beginTransaction();
        try {
            $showtime = Showtime::create([
                'movie_id'   => $movie->id,
                'room_id'    => $room->id,
                'show_date'  => $showDateStr,
                'start_time' => $startStr,
                'end_time'   => $endStr,
                'base_price' => $request->integer('base_price'),
                'status'     => 'upcoming',
            ]);

            $showtimeSeatsData = [];
            Seat::where('room_id', $room->id)->select('id')->chunk(500, function ($seats) use ($showtime, &$showtimeSeatsData) {
                foreach ($seats as $seat) {
                    $showtimeSeatsData[] = [
                        'showtime_id' => $showtime->id,
                        'seat_id'     => $seat->id,
                        'user_id'     => null,
                        'status'      => 'available',
                        'locked_at'   => null,
                        'expires_at'  => null,
                    ];
                }
            });

            if (empty($showtimeSeatsData)) {
                DB::rollBack();
                return back()->withInput()->withErrors([
                    'room_id' => 'Phòng chiếu này chưa được thiết lập sơ đồ ghế. Vui lòng cấu hình ghế trước.',
                ]);
            }

            ShowtimeSeat::insert($showtimeSeatsData);

            DB::commit();

            return redirect()
                ->route('manager.showtimes.index', ['date' => $showDateStr])
                ->with('success', sprintf(
                    'Tạo suất chiếu "%s" thành công! Đã sinh sơ đồ %d ghế trống.',
                    $movie->title,
                    count($showtimeSeatsData)
                ));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi tạo suất chiếu', [
                'user_id'  => Auth::id(),
                'room_id'  => $room->id,
                'movie_id' => $movie->id,
                'error'    => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại hoặc liên hệ quản trị viên.',
            ]);
        }
    }

    public function cancelShowtime($id)
    {
        $cinemaIds = $this->getCinemaIds();

        // Bước 1: Xác thực quyền — suất phải thuộc rạp của manager
        $showtime = Showtime::whereIn('room_id', function ($query) use ($cinemaIds) {
            $query->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds)->whereNull('deleted_at');
        })->findOrFail($id);

        // Không cho hủy suất đang chiếu, đã kết thúc, hoặc đã hủy
        if (in_array($showtime->status, ['showing', 'finished', 'cancelled'])) {
            return back()->with('error', 'Không thể hủy suất chiếu đang chiếu, đã kết thúc hoặc đã hủy trước đó.');
        }

        // Bước 2: Pre-flight check — không được có ghế 'booked'
        $hasBookedSeat = ShowtimeSeat::where('showtime_id', $id)->where('status', 'booked')->exists();

        if ($hasBookedSeat) {
            return back()->with('error', 'Không thể hủy suất chiếu đã có khách mua vé. Vui lòng thực hiện quy trình hoàn tiền (Refund) trước.');
        }

        // Bước 3 & 4: Transaction — xóa ghế + soft delete suất chiếu
        DB::transaction(function () use ($showtime) {
            ShowtimeSeat::where('showtime_id', $showtime->id)->delete();
            $showtime->update(['status' => 'cancelled']);
            $showtime->delete();
        });

        // Bước 5: Audit log
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'CANCEL_SHOWTIME',
            'model_type'  => 'Showtime',
            'model_id'    => $showtime->id,
            'description' => 'Manager đã hủy suất chiếu ID=' . $showtime->id,
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->route('manager.showtimes.index')->with('success', 'Suất chiếu đã được hủy thành công.');
    }

    public function seatStatus($id)
    {
        $cinemaIds = $this->getCinemaIds();

        $showtime = Showtime::whereIn('room_id', function ($query) use ($cinemaIds) {
            $query->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds)->whereNull('deleted_at');
        })
            ->with(['movie', 'room', 'room.cinema'])
            ->findOrFail($id);

        $showtimeSeats = ShowtimeSeat::where('showtime_id', $showtime->id)
            ->with(['seat', 'seat.seatType'])
            ->get();

        $seatsGrouped = $showtimeSeats->groupBy(fn($item) => $item->seat->seat_row)->sortKeys();

        $stats = [
            'total'     => $showtimeSeats->count(),
            'available' => $showtimeSeats->where('status', 'available')->count(),
            'holding'   => $showtimeSeats->where('status', 'holding')->count(),
            'booked'    => $showtimeSeats->where('status', 'booked')->count(),
        ];

        return view('manager.showtimes.seats', compact('showtime', 'seatsGrouped', 'stats'));
    }
}
