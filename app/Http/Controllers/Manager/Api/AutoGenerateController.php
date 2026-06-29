<?php

namespace App\Http\Controllers\Manager\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
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
            'room_id'        => 'required|exists:rooms,id',
            'show_date'      => 'required|date_format:Y-m-d|after_or_equal:today',
            'shift_start'    => 'required|date_format:H:i',
            'shift_end'      => 'required|date_format:H:i|after:shift_start',
            'cleaning_time'  => 'required|integer|min:0',
            'standard_price' => 'required|integer|min:0',
        ], [
            'shift_end.after'          => 'Giờ đóng ca phải sau giờ mở ca.',
            'show_date.after_or_equal' => 'Ngày chiếu không được là ngày trong quá khứ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $movieId       = (int) $request->input('movie_id');
        $roomId        = (int) $request->input('room_id');
        $showDate      = $request->input('show_date');
        $shiftStartStr = $request->input('shift_start');
        $shiftEndStr   = $request->input('shift_end');
        $cleaningTime  = (int) $request->input('cleaning_time');
        $standardPrice = (int) $request->input('standard_price');

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

                // Nếu không trùng, áp dụng quy tắc tính giá thực tế và đưa vào danh sách hợp lệ
                $actualPrice = $this->applyPriceRules($standardPrice, $proposedStart);

                $validShowtimes[] = [
                    'movie_id'   => $movieId,
                    'room_id'    => $roomId,
                    'show_date'  => $showDate,
                    'start_time' => $proposedStart->format('H:i:s'),
                    'end_time'   => $proposedEnd->format('H:i:s'),
                    'base_price' => $actualPrice,
                    'status'     => 'upcoming',
                    'created_at' => now(),
                    'updated_at' => now(),
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
            $seats = Seat::where('room_id', $roomId)->pluck('id')->toArray();

            if (empty($seats)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Phòng chiếu này chưa được thiết lập sơ đồ ghế. Không thể tạo suất chiếu.'
                ], 400);
            }

            // Tạo danh sách liên kết Ghế - Suất chiếu
            // Lưu ý: Bảng showtime_seats KHÔNG có cột timestamps (created_at/updated_at)
            $showtimeSeatsData = [];
            foreach ($newShowtimeIds as $showtimeId) {
                foreach ($seats as $seatId) {
                    $showtimeSeatsData[] = [
                        'showtime_id' => $showtimeId,
                        'seat_id'     => $seatId,
                        'user_id'     => null,
                        'status'      => 'available',
                        'locked_at'   => null,
                        'expires_at'  => null,
                    ];
                }
            }

            // Thực hiện insert bulk theo từng chunk 500 bản ghi nhằm tránh lỗi vượt quá dung lượng gói của MySQL
            $chunks = array_chunk($showtimeSeatsData, 500);
            foreach ($chunks as $chunk) {
                ShowtimeSeat::insert($chunk);
            }

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
     * Phương thức placeholder tính toán phụ thu ngày lễ hoặc quy tắc giá theo ngày/giờ.
     *
     * @param  int  $standardPrice
     * @param  \Carbon\Carbon  $proposedStart
     * @return int
     */
    private function applyPriceRules($standardPrice, Carbon $proposedStart)
    {
        $actualPrice = $standardPrice;
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

        // Đảm bảo giá không âm (trường hợp rule có adjustment_amount âm quá lớn)
        return max(0, $actualPrice);
    }
}
