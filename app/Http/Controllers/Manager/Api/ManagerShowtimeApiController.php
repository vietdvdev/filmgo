<?php

namespace App\Http\Controllers\Manager\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Holiday;
use App\Models\PriceRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ManagerShowtimeApiController extends Controller
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
     * Lấy danh sách các cụm rạp mà Quản lý đang đăng nhập phụ trách.
     * Join bảng cinemas và user_cinemas.
     */
    public function myCinemas()
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'admin')->exists()) {
            $cinemas = \App\Models\Cinema::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'address', 'city']);
            return response()->json($cinemas);
        }

        $cinemas = \App\Models\Cinema::join('user_cinemas', 'cinemas.id', '=', 'user_cinemas.cinema_id')
            ->where('user_cinemas.user_id', $user->id)
            ->where('cinemas.status', 'active')
            ->whereNull('cinemas.deleted_at')
            ->orderBy('cinemas.name')
            ->select('cinemas.id', 'cinemas.name', 'cinemas.address', 'cinemas.city')
            ->get();

        return response()->json($cinemas);
    }

    /**
     * Lấy danh sách phòng hoạt động bình thường (status = 'active') thuộc về một cinema_id cụ thể.
     */
    public function roomsByCinema($cinemaId)
    {
        $cinemaIds = $this->getCinemaIds();

        if (!in_array((int)$cinemaId, $cinemaIds)) {
            return response()->json([
                'message' => 'Bạn không có quyền quản lý rạp chiếu này.'
            ], 403);
        }

        $rooms = Room::where('cinema_id', $cinemaId)
            ->where('status', 'active')
            ->orderBy('room_name')
            ->get(['id', 'room_name', 'room_type', 'capacity', 'cinema_id']);

        return response()->json($rooms);
    }


    /**
     * API kiểm tra trùng lịch chiếu (Overlap Check).
     */
    public function checkOverlap(Request $request)
    {
        $request->validate([
            'room_id'    => 'required|exists:rooms,id',
            'show_date'  => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'movie_id'   => 'required|exists:movies,id',
        ]);

        $room = Room::findOrFail($request->integer('room_id'));
        $movie = Movie::findOrFail($request->integer('movie_id'));

        // Kiểm tra quyền quản lý rạp của Manager đối với phòng này
        if (!Auth::user()->roles()->where('name', 'admin')->exists()) {
            $cinemaIds = $this->getCinemaIds();
            if (!in_array($room->cinema_id, $cinemaIds)) {
                return response()->json(['message' => 'Bạn không có quyền quản lý rạp của phòng chiếu này.'], 403);
            }
        }

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->input('show_date') . ' ' . $request->input('start_time'),
            config('app.timezone')
        );

        if ($startTime->isPast()) {
            return response()->json([
                'overlap'  => true,
                'past'     => true,
                'message'  => 'Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.',
            ]);
        }

        $endTime = $startTime->copy()->addMinutes($movie->duration);

        $startTimeStr = $startTime->format('H:i:s');
        $endTimeStr   = $endTime->format('H:i:s');
        $showDateStr  = $request->input('show_date');

        $overlapQuery = Showtime::where('room_id', $room->id)
            ->where('show_date', $showDateStr);

        if ($endTimeStr <= $startTimeStr) {
            $overlapQuery->where('start_time', '>=', $startTimeStr);
        } else {
            $overlapQuery->where(function ($q) use ($startTimeStr, $endTimeStr) {
                $q->where('start_time', '<', $endTimeStr)
                  ->where('end_time', '>', $startTimeStr);
            });
        }

        $overlap = $overlapQuery->with('movie')->first();

        if ($overlap) {
            $overlapStart = Carbon::parse($overlap->start_time)->format('H:i');
            $overlapEnd   = Carbon::parse($overlap->end_time)->format('H:i');
            return response()->json([
                'overlap' => true,
                'message' => sprintf(
                    'Trùng lịch! Khung giờ %s–%s trùng với suất chiếu "%s" (%s–%s) tại phòng này.',
                    $startTime->format('H:i'),
                    $endTime->format('H:i'),
                    $overlap->movie->title,
                    $overlapStart,
                    $overlapEnd
                )
            ]);
        }

        return response()->json([
            'overlap' => false,
            'message' => 'Khung giờ này trống, có thể xếp lịch.'
        ]);
    }

    /**
     * API tính toán giá vé cơ sở gợi ý (Suggest Price).
     */
    public function suggestPrice(Request $request)
    {
        $request->validate([
            'show_date'  => 'required|date',
            'start_time' => 'required|date_format:H:i',
        ]);

        $showDate = Carbon::parse($request->input('show_date'));
        $startTimeStr = $request->input('start_time') . ':00';

        $basePrice = 80000; // Giá mặc định
        $reason = [];

        // 1. Kiểm tra ngày lễ
        $isHoliday = Holiday::whereDate('holiday_date', $showDate->toDateString())->first();
        if ($isHoliday) {
            $basePrice += 20000;
            $reason[] = "Ngày lễ: " . $isHoliday->name . " (+20,000đ)";
        }

        // 2. Kiểm tra Price Rules
        $dayOfWeek = $showDate->dayOfWeek; // 0 (CN) -> 6 (T7)
        $rule = PriceRule::where('is_active', 1)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $startTimeStr)
            ->where('end_time', '>=', $startTimeStr)
            ->first();

        if ($rule) {
            $basePrice += $rule->adjustment_amount;
            $sign = $rule->adjustment_amount >= 0 ? '+' : '';
            $reason[] = "Quy tắc giá \"" . $rule->name . "\": (" . $sign . number_format($rule->adjustment_amount) . "đ)";
        }

        return response()->json([
            'suggested_price' => $basePrice,
            'reason' => count($reason) > 0 ? implode(', ', $reason) : 'Giá cơ bản ngày thường'
        ]);
    }

    /**
     * API tạo suất chiếu mới.
     */
    public function store(StoreShowtimeRequest $request)
    {
        $room = Room::findOrFail($request->integer('room_id'));

        if ($room->status !== 'active') {
            return response()->json([
                'errors' => ['room_id' => ['Phòng chiếu này hiện không hoạt động.']]
            ], 422);
        }

        // Check policy
        Gate::authorize('create', [Showtime::class, $room]);

        $movie = Movie::findOrFail($request->integer('movie_id'));

        if ($movie->status === 'stopped') {
            return response()->json([
                'errors' => ['movie_id' => ['Phim này đã ngừng chiếu, không thể tạo suất chiếu mới.']]
            ], 422);
        }

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->input('show_date') . ' ' . $request->input('start_time')
        );
        $endTime = $startTime->copy()->addMinutes($movie->duration);

        $startTimeStr = $startTime->format('H:i:s');
        $endTimeStr   = $endTime->format('H:i:s');
        $showDateStr  = $request->input('show_date');

        // Check collision
        $overlapQuery = Showtime::where('room_id', $room->id)
            ->where('show_date', $showDateStr);

        if ($endTimeStr <= $startTimeStr) {
            $overlapQuery->where('start_time', '>=', $startTimeStr);
        } else {
            $overlapQuery->where(function ($q) use ($startTimeStr, $endTimeStr) {
                $q->where('start_time', '<', $endTimeStr)
                  ->where('end_time', '>', $startTimeStr);
            });
        }

        $overlap = $overlapQuery->with('movie')->first();

        if ($overlap) {
            $overlapStart = Carbon::parse($overlap->start_time)->format('H:i');
            $overlapEnd   = Carbon::parse($overlap->end_time)->format('H:i');

            return response()->json([
                'errors' => [
                    'start_time' => [
                        sprintf(
                            'Trùng lịch! Khung giờ %s–%s bị chồng lấn với suất chiếu "%s" (%s–%s) tại phòng này.',
                            $startTime->format('H:i'),
                            $endTime->format('H:i'),
                            $overlap->movie->title,
                            $overlapStart,
                            $overlapEnd
                        )
                    ]
                ]
            ], 422);
        }

        $publishAtInput = $request->input('publish_at');
        $publishAt = $publishAtInput ? Carbon::parse($publishAtInput, 'Asia/Ho_Chi_Minh')->setTimezone(config('app.timezone')) : null;
        $status = ($publishAt === null || $publishAt->lte(now())) ? 'active' : 'upcoming';

        DB::beginTransaction();
        try {
            $showtime = Showtime::create([
                'movie_id'   => $movie->id,
                'room_id'    => $room->id,
                'show_date'  => $showDateStr,
                'start_time' => $startTimeStr,
                'end_time'   => $endTimeStr,
                'base_price' => $request->integer('base_price'),
                'status'     => $status,
                'publish_at' => $publishAt,
            ]);

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
                return response()->json([
                    'errors' => ['room_id' => ['Phòng chiếu này chưa được thiết lập sơ đồ ghế. Vui lòng cấu hình ghế trước.']]
                ], 422);
            }

            ShowtimeSeat::insert($showtimeSeatsData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Tạo suất chiếu "%s" thành công! Đã sinh sơ đồ %d ghế trống.',
                    $movie->title,
                    count($showtimeSeatsData)
                ),
                'redirect' => route('manager.showtimes.index', ['date' => $showDateStr])
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi tạo suất chiếu API', [
                'user_id'  => Auth::id(),
                'room_id'  => $room->id,
                'movie_id' => $movie->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'errors' => ['system' => ['Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.']]
            ], 500);
        }
    }
}
