<?php

namespace App\Http\Controllers\Manager\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Format;
use App\Models\Holiday;
use App\Models\PriceRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AutoGenerateController extends Controller
{
    /**
     * API Tự động xếp lịch chiếu hàng loạt (Batch Auto-Generate Showtimes).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoGenerate(Request $request)
    {
        // 1. ENDPOINT & PAYLOAD VALIDATION
        $validator = Validator::make($request->all(), [
            'movie_id'       => 'required|exists:movies,id',
            'format_id'      => 'required|exists:formats,id',
            'room_id'        => 'required|exists:rooms,id',
            'show_date'      => 'required|date_format:Y-m-d|after_or_equal:today',
            'shift_start'    => 'required|date_format:H:i',
            'shift_end'      => 'required|date_format:H:i|after:shift_start',
            'cleaning_time'  => 'required|integer|min:0',
            'standard_price' => 'required|integer|min:0',
        ], [
            'shift_end.after'          => 'Giờ đóng ca phải sau giờ mở ca.',
            'show_date.after_or_equal' => 'Ngày chiếu không được là ngày trong quá khứ.',
            'format_id.required'       => 'Vui lòng chọn định dạng chiếu.',
            'format_id.exists'         => 'Định dạng chiếu không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $shiftStart = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->input('show_date') . ' ' . $request->input('shift_start')
        );
        if ($shiftStart->isPast()) {
            return response()->json([
                'success' => false,
                'errors'  => [
                    'shift_start' => ['Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.'],
                ],
                'message' => 'Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.',
            ], 422);
        }

        $movieId       = (int) $request->input('movie_id');
        $formatId      = (int) $request->input('format_id');
        $roomId        = (int) $request->input('room_id');
        $showDate      = $request->input('show_date');
        $shiftStartStr = $request->input('shift_start');
        $shiftEndStr   = $request->input('shift_end');
        $cleaningTime  = (int) $request->input('cleaning_time');
        $standardPrice = (int) $request->input('standard_price');
        $status = 'active';

        try {
            // 2. CORE ALGORITHM: SMART SLOT FINDING
            // Lấy thời lượng phim (duration)
            $movie = Movie::findOrFail($movieId);
            $duration = (int) $movie->duration;

            if ($movie->status === 'stopped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Phim này đã dừng chiếu, không thể xếp lịch.'
                ], 422);
            }

            // Lấy thông tin phòng và kiểm tra hoạt động
            $room = Room::findOrFail($roomId);
            if ($room->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Phòng chiếu này hiện không hoạt động.'
                ], 422);
            }

            // Kiểm tra tương thích Định dạng chiếu giữa Phim và Phòng
            $formatService = app(\App\Services\FormatService::class);
            $formatErrors  = $formatService->validateShowtimeFormatAndRoom($movieId, $formatId, $roomId);
            if (!empty($formatErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => reset($formatErrors)
                ], 422);
            }

            // Kiểm tra quyền: Manager chỉ được xếp lịch cho phòng thuộc rạp mình quản lý
            $user = Auth::user();
            $isAdmin = $user->roles()->where('name', 'admin')->exists();
            if (!$isAdmin) {
                $allowedCinemaIds = $user->cinemas()->pluck('cinemas.id')->toArray();
                if (!in_array($room->cinema_id, $allowedCinemaIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền xếp lịch cho phòng chiếu này.'
                    ], 403);
                }
            }

            // Phụ thu lấy theo định dạng đã cấu hình cho phòng.
            // Format của suất vẫn là format phim, nhưng phòng 3D/4DX vẫn có phụ thu riêng.
            $room->load('format');
            $roomFormat = $room->format;
            if (!$roomFormat) {
                $roomFormatName = strtoupper(trim((string) $room->room_type));
                $roomFormatName = $roomFormatName === '4D' ? '4DX' : $roomFormatName;
                $roomFormat = Format::whereRaw('UPPER(name) = ?', [$roomFormatName])->first();
            }
            $format = Format::find($formatId);
            $formatSurcharge = $roomFormat
                ? (int) $roomFormat->surcharge_price
                : ($format ? (int) $format->surcharge_price : 0);

            // Truy vấn toàn bộ các suất chiếu hiện có của phòng trong ngày chỉ định, xếp tăng dần theo giờ bắt đầu
            $existingShowtimes = Showtime::where('room_id', $roomId)
                ->where('show_date', $showDate)
                ->orderBy('start_time', 'asc')
                ->get(['start_time', 'end_time']);

            // Chuyển đổi dữ liệu suất chiếu hiện có thành các đối tượng Carbon để tiện so sánh
            $existingSlots = $existingShowtimes->map(function ($showtime) use ($showDate) {
                return [
                    'start' => Carbon::createFromFormat('Y-m-d H:i:s', $showDate . ' ' . $showtime->start_time),
                    'end'   => Carbon::createFromFormat('Y-m-d H:i:s', $showDate . ' ' . $showtime->end_time),
                ];
            })->toArray();

            // Khởi tạo mốc thời gian bắt đầu ca và kết thúc ca dưới dạng Carbon
            $shiftStart = Carbon::createFromFormat('Y-m-d H:i', $showDate . ' ' . $shiftStartStr);
            $shiftEnd   = Carbon::createFromFormat('Y-m-d H:i', $showDate . ' ' . $shiftEndStr);

            $currentTime = $shiftStart->copy();
            $validShowtimes = [];

            // Vòng lặp tìm kiếm slot trống thông minh
            while ($currentTime->copy()->addMinutes($duration)->lte($shiftEnd)) {
                $proposedStart = $currentTime->copy();
                $proposedEnd   = $proposedStart->copy()->addMinutes($duration);

                $hasOverlap = false;
                $overlapEndTime = null;

                // Kiểm tra xung đột với toàn bộ suất chiếu hiện có
                foreach ($existingSlots as $slot) {
                    // Công thức overlap: (ProposedStart < ExistingEnd) AND (ProposedEnd > ExistingStart)
                    if ($proposedStart->lt($slot['end']) && $proposedEnd->gt($slot['start'])) {
                        $hasOverlap = true;
                        $overlapEndTime = $slot['end']->copy();
                        break;
                    }
                }

                if ($hasOverlap) {
                    // Nếu trùng lịch, nhảy pointer thời gian đến: (Thời điểm kết thúc của suất chiếu trùng + Thời gian dọn dẹp)
                    $currentTime = $overlapEndTime->copy()->addMinutes($cleaningTime);
                    continue;
                }

                // Nếu không trùng, áp dụng quy tắc tính giá thực tế (bao gồm Phụ thu định dạng + Ngày lễ + Price Rules)
                $actualPrice = $this->applyPriceRules($standardPrice, $proposedStart, $formatSurcharge);

                $validShowtimes[] = [
                    'movie_id'          => $movieId,
                    'format_id'         => $formatId,
                    'room_id'           => $roomId,
                    'show_date'         => $showDate,
                    'start_time'        => $proposedStart->format('H:i:s'),
                    'end_time'          => $proposedEnd->format('H:i:s'),
                    'base_price'        => $actualPrice,
                    'status'            => $status,
                    'publish_at'        => null,
                    // [v2.0] Đánh dấu suất do hệ thống TỰ ĐỘNG sinh (phân biệt với tạo tay)
                    'is_auto_generated' => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                // Di chuyển pointer thời gian: (Thời điểm kết thúc suất đề xuất + Thời gian dọn dẹp)
                $currentTime = $proposedEnd->copy()->addMinutes($cleaningTime);
            }

            if (empty($validShowtimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy khoảng thời gian trống nào phù hợp để xếp lịch tự động trong ca làm việc này.'
                ], 400);
            }

            // 3. DATABASE OPTIMIZATION (TRANSACTION & BULK INSERT)
            DB::beginTransaction();

            // Thực hiện Bulk Insert các suất chiếu mới
            Showtime::insert($validShowtimes);

            // Lấy danh sách các suất chiếu mới tạo để sinh sơ đồ ghế
            $startTimeList = array_map(function ($s) {
                return $s['start_time'];
            }, $validShowtimes);

            $newShowtimeIds = Showtime::where('room_id', $roomId)
                ->where('show_date', $showDate)
                ->whereIn('start_time', $startTimeList)
                ->pluck('id')
                ->toArray();

            // Lấy toàn bộ danh sách ghế đang hoạt động của phòng chiếu này
            // [v2.0] Tối ưu: Lấy danh sách ghế và seat_type_id
            $seats = Seat::where('room_id', $roomId)
                ->select('id', 'seat_type_id')
                ->get();

            if ($seats->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Phòng chiếu này chưa được thiết lập sơ đồ ghế. Không thể tạo suất chiếu.'
                ], 400);
            }

            // [v2.0] Pre-load toàn bộ SeatType vào map [id => surcharge_price] — chỉ 1 query duy nhất
            // Dùng surcharge_price từ Model để Admin có thể tùy chỉnh giá phụ thu mà không cần sửa code
            // Công thức: showtime_seat.price = showtime.base_price + seat_type.surcharge_price
            $surchargeMap = SeatType::all()->pluck('surcharge_price', 'id'); // Collection: [typeId => surcharge]

            // [v2.0] Lấy map showtime_id → base_price để tránh query lặp lại trong vòng lặp
            // Ví dụ: [101 => 80000, 102 => 95000, 103 => 80000]
            $showtimeBasePrices = Showtime::whereIn('id', $newShowtimeIds)
                ->pluck('base_price', 'id'); // Collection: [id => base_price]

            // [v2.0] Tạo danh sách liên kết Ghế-Suất chiếu bằng Collection flatMap (thay thế double foreach)
            // Ưu điểm: mạch lạc, dễ đọc; Collection lazy flatten() thay cho nối mảng thủ công
            $showtimeSeatsData = collect($newShowtimeIds)
                ->flatMap(function (int $showtimeId) use ($seats, $surchargeMap, $showtimeBasePrices) {
                    // Lấy base_price của suất chiếu này (fallback = 0 nếu không tìm thấy)
                    $basePrice = (int) ($showtimeBasePrices[$showtimeId] ?? 0);

                    return $seats->map(function ($seat) use ($showtimeId, $basePrice, $surchargeMap) {
                        // Đọc surcharge_price từ map đã pre-load; fallback = 0 nếu ghế chưa gán type
                        $surcharge = (int) ($surchargeMap[$seat->seat_type_id] ?? 0);

                        return [
                            'showtime_id' => $showtimeId,
                            'seat_id'     => $seat->id,
                            'user_id'     => null,
                            'status'      => 'available',
                            // [v2.0] Snapshot giá tại thời điểm tạo suất: base_price + surcharge_price loại ghế
                            // Backend sau này chỉ cần SUM(price) để tính tổng giỏ hàng mà không tính lại công thức
                            'price'       => $basePrice + $surcharge,
                            'locked_at'   => null,
                            'expires_at'  => null,
                        ];
                    });
                })
                ->values()  // Re-index trước khi chunk
                ->toArray();

            // Thực hiện insert bulk theo từng chunk 500 bản ghi
            // Tránh lỗi MySQL "max_allowed_packet" khi có hàng ngàn ghế × nhiều suất chiếu
            collect($showtimeSeatsData)
                ->chunk(500)
                ->each(fn ($chunk) => ShowtimeSeat::insert($chunk->values()->toArray()));

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Tự động xếp lịch chiếu hàng loạt thành công!',
                'total_generated' => count($validShowtimes),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi tự động xếp lịch chiếu hàng loạt: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống khi tự động xếp lịch chiếu.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Phương thức tính toán phụ thu (Định dạng + Ngày lễ + Price Rules theo ngày/giờ).
     *
     * @param  int  $standardPrice
     * @param  \Carbon\Carbon  $proposedStart
     * @param  int  $formatSurcharge
     * @return int
     */
    private function applyPriceRules($standardPrice, Carbon $proposedStart, int $formatSurcharge = 0)
    {
        $actualPrice = $standardPrice + $formatSurcharge;
        $startTimeStr = $proposedStart->format('H:i:s');

        // 1. Kiểm tra ngày lễ
        $isHoliday = Holiday::whereDate('holiday_date', $proposedStart->toDateString())->first();
        if ($isHoliday) {
            $actualPrice += 20000;
        }

        // 2. Kiểm tra Price Rules
        $dayOfWeek = $proposedStart->dayOfWeek; // 0 (CN) -> 6 (T7)
        $rule = PriceRule::where('is_active', 1)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $startTimeStr)
            ->where('end_time', '>=', $startTimeStr)
            ->first();

        if ($rule) {
            $actualPrice += $rule->adjustment_amount;
        }

        // Đảm bảo giá không âm
        return max(0, $actualPrice);
    }
}
