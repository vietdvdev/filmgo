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
use App\Models\Format;
use App\Services\FormatService;
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
     * BƯỚC 1: Cascading Dropdown - Lấy danh sách định dạng (formats) được hỗ trợ bởi bộ phim.
     * GET /manager/showtimes/api/formats-by-movie/{movieId}
     */
    public function getFormatsByMovie($movieId, FormatService $formatService)
    {
        $formats = $formatService->getFormatsByMovie((int) $movieId);

        return response()->json($formats);
    }

    /**
     * BƯỚC 2: Cascading Dropdown - Lấy danh sách các phòng chiếu phù hợp với định dạng đã chọn.
     * GET /manager/showtimes/api/compatible-rooms?cinema_id=1&format_id=2
     */
    public function getCompatibleRooms(Request $request, FormatService $formatService)
    {
        $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'format_id' => 'required|exists:formats,id',
        ]);

        $cinemaId = $request->integer('cinema_id');
        $formatId = $request->integer('format_id');

        $cinemaIds = $this->getCinemaIds();
        if (!in_array($cinemaId, $cinemaIds, true)) {
            return response()->json([
                'message' => 'Bạn không có quyền quản lý rạp chiếu này.'
            ], 403);
        }

        $rooms = $formatService->getCompatibleRooms($cinemaId, $formatId);

        return response()->json($rooms);
    }

    /**
     * Helper: Lấy danh sách tên định dạng chiếu được hỗ trợ bởi loại phòng.
     */
    private function getSupportedFormatsByRoomType(string $roomType): array
    {
        $typeUpper = strtoupper(trim($roomType));

        return match ($typeUpper) {
            '2D'    => ['2D'],
            '3D'    => ['2D', '3D'],
            'IMAX'  => ['2D', '3D', 'IMAX'],
            '4DX'   => ['2D', '3D', '4DX'],
            default => ['2D', $typeUpper],
        };
    }

    /**
     * API: Luồng Ưu Tiên Phim (Movie-first) - Lấy danh sách Phòng chiếu tương thích với Phim.
     * GET /manager/showtimes/api/rooms-by-movie/{movieId}
     */
    public function getRoomsByMovie($movieId)
    {
        $movie = Movie::with('formats:id,name')->findOrFail($movieId);
        $cinemaIds = $this->getCinemaIds();

        $movieFormatNames = $movie->formats->pluck('name')->toArray();

        if (empty($movieFormatNames)) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Phim này chưa được gán định dạng chiếu nào.',
            ]);
        }

        $rooms = Room::whereIn('cinema_id', $cinemaIds)
            ->where('status', 'active')
            ->with('cinema:id,name')
            ->get(['id', 'room_name', 'room_type', 'capacity', 'cinema_id'])
            ->filter(function ($room) use ($movieFormatNames) {
                $supported = $this->getSupportedFormatsByRoomType($room->room_type);
                return count(array_intersect($movieFormatNames, $supported)) > 0;
            })
            ->map(function ($room) {
                $room->cinema_name = $room->cinema?->name;
                return $room;
            })
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $rooms,
        ]);
    }

    /**
     * API 1: Luồng Ưu Tiên Phòng (Room-first) - Lấy danh sách Phim tương thích với Phòng chiếu.
     * GET /api/rooms/{id}/movies
     */
    public function getMoviesByRoom($id)
    {
        $room = Room::findOrFail($id);

        $cinemaIds = $this->getCinemaIds();
        if (!in_array((int)$room->cinema_id, $cinemaIds, true)) {
            return response()->json([
                'message' => 'Bạn không có quyền quản lý phòng chiếu này.'
            ], 403);
        }

        $supportedFormats = $this->getSupportedFormatsByRoomType($room->room_type);

        $movies = Movie::where('status', '!=', 'stopped')
            ->whereHas('formats', function ($q) use ($supportedFormats) {
                $q->whereIn('name', $supportedFormats);
            })
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'age_limit', 'status']);

        return response()->json([
            'success' => true,
            'room'    => [
                'id'        => $room->id,
                'name'      => $room->room_name,
                'room_type' => $room->room_type,
            ],
            'data'    => $movies,
        ]);
    }

    /**
     * API 2: Luồng Ưu Tiên Phòng (Room-first) - Lấy danh sách Định dạng giao điểm giữa Phòng và Phim.
     * GET /api/rooms/{room_id}/movies/{movie_id}/formats
     */
    public function getIntersectionFormats($roomId, $movieId)
    {
        $room  = Room::findOrFail($roomId);
        $movie = Movie::findOrFail($movieId);

        $cinemaIds = $this->getCinemaIds();
        if (!in_array((int)$room->cinema_id, $cinemaIds, true)) {
            return response()->json([
                'message' => 'Bạn không có quyền quản lý phòng chiếu này.'
            ], 403);
        }

        $roomFormats = $this->getSupportedFormatsByRoomType($room->room_type);

        $formats = Format::whereHas('movies', function ($q) use ($movie) {
                $q->where('movies.id', $movie->id);
            })
            ->whereIn('name', $roomFormats)
            ->orderBy('id')
            ->get(['id', 'name', 'surcharge_price']);

        return response()->json([
            'success' => true,
            'data'    => $formats,
        ]);
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
            ->where('show_date', $showDateStr)
            ->where('status', '!=', 'cancelled');  // Bỏ qua suất đã hủy

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
            'format_id'  => 'nullable|exists:formats,id',
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

        // 3. Kiểm tra Price Rules
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
            $request->input('show_date') . ' ' . $request->input('start_time'),
            config('app.timezone')  // Bug fix: luôn chỉ định timezone tránh lệch giờ
        );
        $endTime = $startTime->copy()->addMinutes($movie->duration);

        $startTimeStr = $startTime->format('H:i:s');
        $endTimeStr   = $endTime->format('H:i:s');
        $showDateStr  = $request->input('show_date');
        // Bug fix: Xử lý trường hợp suất chiếu qua nửa đêm
        $crossMidnight = $endTime->toDateString() > $startTime->toDateString();

        // Bug fix: Dùng so sánh Carbon datetime tuyệt đối thay vì string để xử lý đúng suất qua nửa đêm
        $overlapQuery = Showtime::where('room_id', $room->id)
            ->whereIn('show_date', [
                $showDateStr,
                // Nếu qua nửa đêm, cũng kiểm tra ngày hôm sau
                $crossMidnight ? $endTime->toDateString() : $showDateStr,
            ])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTimeStr, $endTimeStr, $crossMidnight, $showDateStr) {
                if ($crossMidnight) {
                    // Suất chiếu mới qua nửa đêm: trùng với bất kỳ suất nào bắt đầu từ start đến 23:59 hoặc 00:00 đến end
                    $q->where(function ($inner) use ($startTimeStr) {
                        $inner->where('show_date', '=', request()->input('show_date'))
                              ->where('start_time', '>=', $startTimeStr);
                    })->orWhere(function ($inner) use ($endTimeStr) {
                        $inner->where('end_time', '>', '00:00:00')
                              ->where('start_time', '<', $endTimeStr);
                    });
                } else {
                    $q->where('start_time', '<', $endTimeStr)
                      ->where('end_time', '>', $startTimeStr);
                }
            });

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
                            $overlap->movie->title ?? 'N/A',
                            $overlapStart,
                            $overlapEnd
                        )
                    ]
                ]
            ], 422);
        }

        $status = 'active';

        DB::beginTransaction();
        try {
            $showtime = Showtime::create([
                'movie_id'   => $movie->id,
                'format_id'  => $request->integer('format_id'),
                'room_id'    => $room->id,
                'show_date'  => $showDateStr,
                'start_time' => $startTimeStr,
                'end_time'   => $endTimeStr,
                'base_price' => $request->integer('base_price'),
                'status'     => $status,
            ]);

            // [v2.0] Pre-load SeatType surcharge map để tránh N+1 query
            $surchargeMap = \App\Models\SeatType::all()->pluck('surcharge_price', 'id');
            $basePrice    = $showtime->base_price;

            $showtimeSeatsData = [];
            \App\Models\Seat::where('room_id', $room->id)
                ->select('id', 'seat_type_id')
                ->chunk(500, function ($seats) use ($showtime, $basePrice, $surchargeMap, &$showtimeSeatsData) {
                    foreach ($seats as $seat) {
                        $surcharge = (int) ($surchargeMap[$seat->seat_type_id] ?? 0);
                        $showtimeSeatsData[] = [
                            'showtime_id' => $showtime->id,
                            'seat_id'     => $seat->id,
                            'user_id'     => null,
                            'status'      => 'available',
                            // [v2.0] Snapshot giá = base_price + surcharge loại ghế
                            'price'       => $basePrice + $surcharge,
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
