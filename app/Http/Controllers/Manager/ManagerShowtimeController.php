<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Models\ActivityLog;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ManagerShowtimeController extends Controller
{
    /**
     * Lấy danh sách ID các rạp mà user hiện tại được phân công.
     * Admin fallback về tất cả rạp.
     *
     * Tối ưu: Cache kết quả trong 10 phút — danh sách rạp phân công ít thay đổi.
     * Cache key gắn với user_id để đảm bảo mỗi user có cache riêng biệt.
     */
    private function getCinemaIds(): array
    {
        $user   = Auth::user();
        $userId = $user->id;

        // Cache key theo user_id để tránh lấn lộn giữa các user
        $cacheKey = "manager_cinema_ids_{$userId}";

        return Cache::remember($cacheKey, 600, function () use ($user) {
            // Kiểm tra role admin một lần duy nhất trong callback, kết quả được cache 10 phút
            if ($user->roles()->where('name', 'admin')->exists()) {
                return Cinema::pluck('id')->toArray();
            }

            $ids = $user->cinemas()->pluck('cinemas.id')->toArray();

            if (empty($ids)) {
                abort(403, 'Tài khoản của bạn chưa được phân công quản lý rạp nào. Vui lòng liên hệ Admin.');
            }

            return $ids;
        });
    }

    public function index(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();

        // Lấy danh sách rạp được phân công quản lý
        $cinemas = Cinema::whereIn('id', $cinemaIds)->get(['id', 'name']);

        // Lấy danh sách phim đang/sắp chiếu
        $movies = Movie::whereIn('status', ['showing', 'upcoming'])
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'age_limit']);

        $showDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))?->startOfDay() ?? Carbon::today()
            : Carbon::today();

        return view('manager.showtimes.index', compact('cinemas', 'movies', 'showDate'));
    }

    /**
     * API Lấy danh sách phòng thuộc rạp cụ thể (Cascading Select)
     */
    public function apiGetRooms(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();
        $cinemaId = $request->integer('cinema_id');

        if (!in_array($cinemaId, $cinemaIds)) {
            return response()->json(['error' => 'Bạn không có quyền truy cập rạp này.'], 403);
        }

        $rooms = Room::where('cinema_id', $cinemaId)
            ->where('status', 'active')
            ->get(['id', 'room_name as name']);

        return response()->json(['rooms' => $rooms]);
    }

    /**
     * API Lấy danh sách suất chiếu có bộ lọc (cinema_id, room_id, movie_id, date)
     */
    public function apiGetShowtimes(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();
        $cinemaId  = $request->integer('cinema_id');
        $roomId    = $request->input('room_id');
        $movieId   = $request->input('movie_id');
        $date      = $request->input('date', today()->toDateString());

        // Kiểm tra quyền đối với rạp được chọn
        if ($cinemaId && !in_array($cinemaId, $cinemaIds)) {
            return response()->json(['error' => 'Bạn không có quyền truy cập rạp này.'], 403);
        }

        // Lấy danh sách ID phòng chiếu hợp lệ của rạp được chọn (hoặc tất cả rạp được quyền quản lý)
        $targetCinemaIds = $cinemaId ? [$cinemaId] : $cinemaIds;
        $allowedRoomIds = Room::whereIn('cinema_id', $targetCinemaIds)->pluck('id')->toArray();

        $query = Showtime::whereIn('room_id', $allowedRoomIds)
            ->whereDate('show_date', $date);

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if ($movieId) {
            $query->where('movie_id', $movieId);
        }

        // Load relations và đếm ghế trống/đặt hiệu năng cao (withCount)
        $showtimes = $query->with([
                'movie:id,title,duration,age_limit',
                'room:id,room_name,cinema_id',
                'room.cinema:id,name'
            ])
            ->withCount([
                'showtimeSeats as total_seats',
                'showtimeSeats as booked_seats' => function ($q) {
                    $q->where('status', 'booked');
                }
            ])
            ->orderBy('start_time')
            ->get()
            ->map(function ($showtime) {
                // Normalize room.name vì Eloquent không hỗ trợ alias trong with() select
                if ($showtime->room) {
                    $showtime->room->name = $showtime->room->room_name;
                }
                return $showtime;
            });

        return response()->json($showtimes);
    }

    /**
     * API Mở bán hàng loạt suất chiếu (Bulk Update upcoming -> active)
     */
    public function apiBulkOpenSales(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:showtimes,id'
        ]);

        $cinemaIds = $this->getCinemaIds();
        $showtimeIds = $request->input('ids');

        // Xác thực quyền: tất cả suất chiếu phải thuộc rạp quản lý
        $count = Showtime::whereIn('id', $showtimeIds)
            ->whereIn('room_id', function ($q) use ($cinemaIds) {
                $q->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds);
            })
            ->count();

        if ($count !== count($showtimeIds)) {
            return response()->json(['error' => 'Bạn không có quyền thao tác trên một số suất chiếu đã chọn.'], 403);
        }

        // Cập nhật trạng thái sang active (Đang mở bán)
        Showtime::whereIn('id', $showtimeIds)
            ->where('status', 'upcoming')
            ->update(['status' => 'active']);

        return response()->json(['success' => true]);
    }

    /**
     * API Xóa/Hủy suất chiếu
     */
    public function apiDeleteShowtime($id)
    {
        $cinemaIds = $this->getCinemaIds();

        $showtime = Showtime::whereIn('room_id', function ($q) use ($cinemaIds) {
            $q->select('id')->from('rooms')->whereIn('cinema_id', $cinemaIds);
        })->findOrFail($id);

        // Pre-flight check: Không được có ghế đã bán (booked)
        $hasBookedSeat = ShowtimeSeat::where('showtime_id', $id)->where('status', 'booked')->exists();
        if ($hasBookedSeat) {
            return response()->json(['error' => 'Suất chiếu đã có ghế được đặt, không thể hủy!'], 400);
        }

        DB::transaction(function () use ($showtime) {
            ShowtimeSeat::where('showtime_id', $showtime->id)->delete();
            $showtime->update(['status' => 'cancelled']);
            $showtime->delete();
        });

        // Audit Log
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'CANCEL_SHOWTIME_API',
            'model_type'  => 'Showtime',
            'model_id'    => $showtime->id,
            'description' => 'Manager đã hủy suất chiếu API ID=' . $showtime->id,
            'ip_address'  => request()->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function create()
    {
        $movies = Movie::whereIn('status', ['showing', 'upcoming'])
            ->with('formats:id,name,surcharge_price')
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'age_limit']);

        return view('manager.showtimes.create', compact('movies'));
    }

    public function showAutoGenerateForm()
    {
        $movies = Movie::whereIn('status', ['showing', 'upcoming'])
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'age_limit']);

        return view('manager.showtimes.auto-generate', compact('movies'));
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

        $startTime   = Carbon::createFromFormat('Y-m-d H:i', $request->input('show_date') . ' ' . $request->input('start_time'), config('app.timezone'));
        $endTime     = $startTime->copy()->addMinutes($movie->duration);
        $startStr    = $startTime->format('H:i:s');
        $endStr      = $endTime->format('H:i:s');
        $showDateStr = $request->input('show_date');

        // Bước 3: Kiểm tra trùng lịch (bỏ qua suất đã hủy)
        $overlapQuery = Showtime::where('room_id', $room->id)
            ->where('show_date', $showDateStr)
            ->where('status', '!=', 'cancelled');

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
                'movie_id'          => $movie->id,
                'format_id'         => $request->integer('format_id'),
                'room_id'           => $room->id,
                'show_date'         => $showDateStr,
                'start_time'        => $startStr,
                'end_time'          => $endStr,
                'base_price'        => $request->integer('base_price'),
                'status'            => 'upcoming',
                // [v2.0] Đánh dấu suất được tạo THỦ CÔNG bởi Manager
                'is_auto_generated' => false,
            ]);

            // [v2.0] Pre-load toàn bộ SeatType vào một map [id => surcharge_price] để tránh N+1 query.
            // Dùng surcharge_price từ Model thay vì hardcode để Admin có thể tùy chỉnh sau này.
            // Công thức: showtime_seat.price = showtime.base_price + seat_type.surcharge_price
            $surchargeMap = SeatType::all()->pluck('surcharge_price', 'id'); // [seatTypeId => surcharge]
            $basePrice    = $showtime->base_price;

            $showtimeSeatsData = [];

            // Eager-load seatType để tránh N+1 query trong vòng lặp chunk
            Seat::where('room_id', $room->id)
                ->with('seatType:id,surcharge_price')
                ->select('id', 'seat_type_id')
                ->chunk(500, function ($seats) use ($showtime, $basePrice, $surchargeMap, &$showtimeSeatsData) {
                    foreach ($seats as $seat) {
                        // Đọc surcharge_price từ relation; fallback = 0 nếu ghế chưa gán type
                        $surcharge = (int) ($surchargeMap[$seat->seat_type_id] ?? 0);

                        $showtimeSeatsData[] = [
                            'showtime_id' => $showtime->id,
                            'seat_id'     => $seat->id,
                            'user_id'     => null,
                            'status'      => 'available',
                            // [v2.0] Snapshot giá = base_price suất + surcharge_price loại ghế
                            'price'       => $basePrice + $surcharge,
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
