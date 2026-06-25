<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
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

class ManagerShowtimeController extends Controller
{
    /**
     * Lấy danh sách ID các rạp mà user hiện tại được phân công.
     */
    private function getCinemaIds(): array
    {
        if (Auth::user()->roles()->where('name', 'admin')->exists()) {
            return \App\Models\Cinema::pluck('id')->toArray();
        }
        return Auth::user()->cinemas()->pluck('cinemas.id')->toArray();
    }

    /**
     * Xem danh sách suất chiếu của rạp theo ngày.
     */
    public function index(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();

        // Dùng today() làm mặc định thay vì Carbon::parse chuỗi tuỳ ý để tránh lỗi inject
        $showDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))?->startOfDay() ?? Carbon::today()
            : Carbon::today();

        $showtimes = Showtime::whereIn('room_id', function ($query) use ($cinemaIds) {
            $query->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds)->whereNull('deleted_at');
        })
            ->whereDate('show_date', $showDate)
            ->with(['movie', 'room', 'room.cinema'])
            ->orderBy('start_time')
            ->paginate(15);

        return view('manager.showtimes.index', compact('showtimes', 'showDate'));
    }

    public function create()
    {
        // Chỉ lấy phim chưa bị soft delete, đang showing hoặc upcoming
        $movies = Movie::whereIn('status', ['showing', 'upcoming'])
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'age_limit']);

        return view('manager.showtimes.create', compact('movies'));
    }


    /**
     * Lưu suất chiếu mới vào database với đầy đủ kiểm tra bảo mật và nghiệp vụ.
     */
    public function store(StoreShowtimeRequest $request)
    {
        // ----------------------------------------------------------------
        // BƯỚC 1: Xác thực quyền truy cập bằng Policy
        // ----------------------------------------------------------------
        // Phải tải Room từ DB để truyền vào Policy (không truyền int thô)
        // findOrFail đã tự kiểm tra soft delete (Room dùng SoftDeletes)
        $room = Room::findOrFail($request->integer('room_id'));

        // Kiểm tra thêm: phòng phải đang active
        if ($room->status !== 'active') {
            return back()->withInput()->withErrors([
                'room_id' => 'Phòng chiếu này hiện không hoạt động.',
            ]);
        }

        // Gate::authorize sẽ ném AuthorizationException (HTTP 403) nếu không có quyền
        Gate::authorize('create', [Showtime::class, $room]);

        // ----------------------------------------------------------------
        // BƯỚC 2: Lấy phim và tính toán end_time
        // ----------------------------------------------------------------
        $movie = Movie::findOrFail($request->integer('movie_id'));

        // Đảm bảo phim còn hiệu lực (không phải trạng thái 'stopped')
        if ($movie->status === 'stopped') {
            return back()->withInput()->withErrors([
                'movie_id' => 'Phim này đã ngừng chiếu, không thể tạo suất chiếu mới.',
            ]);
        }

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->input('show_date') . ' ' . $request->input('start_time')
        );
        $endTime = $startTime->copy()->addMinutes($movie->duration);

        $startTimeStr = $startTime->format('H:i:s');
        $endTimeStr   = $endTime->format('H:i:s');
        $showDateStr  = $request->input('show_date');

        // ----------------------------------------------------------------
        // BƯỚC 3: Kiểm tra trùng lịch (Collision Detection)
        // ----------------------------------------------------------------
        // Chỉ kiểm tra các suất chiếu chưa bị hủy (whereNull deleted_at là default của SoftDeletes)
        // Xử lý trường hợp phim qua nửa đêm:
        // Nếu endTime > 23:59 (qua ngày), cần kiểm tra thêm ngày hôm sau
        $overlapQuery = Showtime::where('room_id', $room->id)
            ->where('show_date', $showDateStr);

        if ($endTimeStr <= $startTimeStr) {
            // Trường hợp phim chiếu qua nửa đêm (end_time nhỏ hơn start_time về mặt string)
            // Trùng lịch nếu: start_time_cu < "24:00" (luôn đúng) AND end_time_cu > start_time_moi
            $overlapQuery->where('start_time', '>=', $startTimeStr);
        } else {
            // Trường hợp thông thường: (start_new < end_old) AND (end_new > start_old)
            $overlapQuery->where(function ($q) use ($startTimeStr, $endTimeStr) {
                $q->where('start_time', '<', $endTimeStr)
                  ->where('end_time', '>', $startTimeStr);
            });
        }

        $overlap = $overlapQuery->with('movie')->first();

        if ($overlap) {
            $overlapStart = Carbon::parse($overlap->start_time)->format('H:i');
            $overlapEnd   = Carbon::parse($overlap->end_time)->format('H:i');

            return back()->withInput()->withErrors([
                'start_time' => sprintf(
                    'Trùng lịch! Khung giờ %s–%s bị chồng lấn với suất chiếu "%s" (%s–%s) tại phòng này.',
                    $startTime->format('H:i'),
                    $endTime->format('H:i'),
                    $overlap->movie->title,
                    $overlapStart,
                    $overlapEnd
                ),
            ]);
        }

        // ----------------------------------------------------------------
        // BƯỚC 4: Transaction – Lưu suất chiếu + sinh sơ đồ ghế
        // ----------------------------------------------------------------
        DB::beginTransaction();
        try {
            // Tạo suất chiếu mới
            $showtime = Showtime::create([
                'movie_id'   => $movie->id,
                'room_id'    => $room->id,
                'show_date'  => $showDateStr,
                'start_time' => $startTimeStr,
                'end_time'   => $endTimeStr,
                'base_price' => $request->integer('base_price'),
                'status'     => 'upcoming',
            ]);

            // Lấy toàn bộ ghế vật lý của phòng (chunk để tránh out-of-memory với phòng lớn)
            $now = now();
            $showtimeSeatsData = [];

            Seat::where('room_id', $room->id)
                ->select('id')
                ->chunk(500, function ($seats) use ($showtime, &$showtimeSeatsData) {
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

            // Log lỗi hệ thống nhưng không tiết lộ chi tiết nội bộ cho người dùng
            \Illuminate\Support\Facades\Log::error('Lỗi tạo suất chiếu', [
                'user_id'  => Auth::id(),
                'room_id'  => $room->id,
                'movie_id' => $movie->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Đã xảy ra lỗi hệ thống trong quá trình xử lý. Vui lòng thử lại hoặc liên hệ quản trị viên.',
            ]);
        }
    }

    /**
     * Hủy suất chiếu (soft delete) — chỉ được hủy suất chiếu của rạp mình quản lý
     * và suất chiếu chưa diễn ra.
     */
    public function cancelShowtime($id)
    {
        $cinemaIds = $this->getCinemaIds();

        $showtime = Showtime::whereIn('room_id', function ($query) use ($cinemaIds) {
            $query->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds)->whereNull('deleted_at');
        })->findOrFail($id);

        // Không cho phép hủy suất chiếu đang chiếu hoặc đã kết thúc
        if (in_array($showtime->status, ['showing', 'finished'])) {
            return redirect()
                ->route('manager.showtimes.index')
                ->withErrors(['error' => 'Không thể hủy suất chiếu đang chiếu hoặc đã kết thúc.']);
        }

        $showtime->delete();

        return redirect()
            ->route('manager.showtimes.index')
            ->with('success', 'Đã hủy suất chiếu thành công.');
    }

    /**
     * Xem tình trạng sơ đồ ghế của một suất chiếu.
     */
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

        // Group seats by seat_row
        $seatsGrouped = $showtimeSeats->groupBy(function ($item) {
            return $item->seat->seat_row;
        })->sortKeys();

        // Stats
        $stats = [
            'total'     => $showtimeSeats->count(),
            'available' => $showtimeSeats->where('status', 'available')->count(),
            'holding'   => $showtimeSeats->where('status', 'holding')->count(),
            'booked'    => $showtimeSeats->where('status', 'booked')->count(),
        ];

        return view('manager.showtimes.seats', compact('showtime', 'seatsGrouped', 'stats'));
    }
}
