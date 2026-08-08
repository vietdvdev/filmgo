<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomSeatSyncService
{
    /**
     * Xác thực quyền: Manager chỉ được thao tác với phòng thuộc rạp mình quản lý.
     * Tham chiếu trực tiếp bảng user_cinemas thay vì dùng relationship lazy để
     * tránh N+1 và đảm bảo kiểm tra chính xác tại tầng DB.
     *
     * @param  int  $roomId   ID phòng chiếu cần kiểm tra
     * @param  int  $userId   ID của Manager đang đăng nhập
     * @return Room           Model Room đã được xác thực
     *
     * @throws \Illuminate\Validation\ValidationException  Nếu không có quyền
     */
    public function authorizeAndFetchRoom(int $roomId, int $userId): Room
    {
        // JOIN rooms → cinemas → user_cinemas để kiểm tra quyền trong 1 query
        $room = Room::select('rooms.*')
            ->join('user_cinemas', 'user_cinemas.cinema_id', '=', 'rooms.cinema_id')
            ->where('rooms.id', $roomId)
            ->where('user_cinemas.user_id', $userId)
            ->whereNull('rooms.deleted_at')
            ->first();

        if (!$room) {
            throw ValidationException::withMessages([
                'room_id' => [
                    'Phòng chiếu không tồn tại hoặc bạn không có quyền quản lý phòng chiếu này.',
                ],
            ]);
        }

        return $room;
    }

    /**
     * Kiểm tra an toàn trước khi chỉnh sửa sơ đồ ghế:
     * 1. CHẶN nếu phòng đang có suất chiếu ĐANG CHIẾU trong khung giờ hiện tại.
     * 2. CHẶN nếu phòng có suất chiếu tương lai ĐÃ CÓ VÉ ĐẶT (booked) hoặc ĐANG GIỮ CHỖ (holding)
     *    để tránh việc xóa ghế làm mất vé đã thanh toán của khách hàng.
     *
     * @param  int  $roomId
     * @throws \Illuminate\Validation\ValidationException
     */
    public function guardAgainstActiveBookingsOrCurrentlyShowing(int $roomId): void
    {
        $nowStr = now()->toDateTimeString();

        // 1. Kiểm tra suất chiếu ĐANG CHIẾU trong giờ hiện tại
        $currentlyShowing = DB::table('showtimes')
            ->join('movies', 'movies.id', '=', 'showtimes.movie_id')
            ->where('showtimes.room_id', $roomId)
            ->where('showtimes.status', '!=', 'cancelled')
            ->where('showtimes.status', '!=', 'finished')
            ->where(function ($q) use ($nowStr) {
                $q->where('showtimes.status', 'showing')
                  ->orWhere(function ($inner) use ($nowStr) {
                      $inner->where(DB::raw("CONCAT(showtimes.show_date, ' ', showtimes.start_time)"), '<=', $nowStr)
                            ->where(DB::raw("CONCAT(showtimes.show_date, ' ', showtimes.end_time)"), '>', $nowStr);
                  });
            })
            ->select('movies.title', 'showtimes.start_time', 'showtimes.end_time')
            ->first();

        if ($currentlyShowing) {
            $startTime = \Carbon\Carbon::parse($currentlyShowing->start_time)->format('H:i');
            $endTime   = \Carbon\Carbon::parse($currentlyShowing->end_time)->format('H:i');

            throw ValidationException::withMessages([
                'seats' => [
                    "Phòng chiếu đang có suất chiếu đang diễn ra (\"{$currentlyShowing->title}\" từ {$startTime} đến {$endTime}). "
                    . "Không thể chỉnh sửa sơ đồ ghế trong thời gian phòng đang chiếu phim!",
                ],
            ]);
        }

        // 2. Kiểm tra xem các suất chiếu tương lai có vé đã đặt (booked) hoặc giữ chỗ (holding) không
        $hasActiveBookings = DB::table('showtime_seats')
            ->join('seats', 'seats.id', '=', 'showtime_seats.seat_id')
            ->join('showtimes', 'showtimes.id', '=', 'showtime_seats.showtime_id')
            ->where('seats.room_id', $roomId)
            ->whereIn('showtime_seats.status', ['booked', 'holding', 'hold'])
            ->where(DB::raw("CONCAT(showtimes.show_date, ' ', showtimes.end_time)"), '>', $nowStr)
            ->exists();

        if ($hasActiveBookings) {
            throw ValidationException::withMessages([
                'seats' => [
                    'Phòng chiếu này đang có vé đã được đặt (hoặc giữ chỗ) cho các suất chiếu sắp tới. '
                    . 'Không thể thay đổi cấu hình sơ đồ ghế để bảo toàn dữ liệu vé của khách hàng!',
                ],
            ]);
        }
    }

    /**
     * Kiểm tra trùng lặp (seat_row + seat_number) trong chính mảng đầu vào
     * trước khi insert để tránh vi phạm ràng buộc UNIQUE(room_id, seat_row, seat_number).
     *
     * @param  array  $seats  Mảng ghế đã được validate cơ bản
     * @throws \Illuminate\Validation\ValidationException
     */
    public function guardAgainstDuplicatesInPayload(array $seats): void
    {
        $seen   = [];
        $errors = [];

        foreach ($seats as $index => $seat) {
            $key = strtoupper(trim($seat['seat_row'])) . '-' . (int)$seat['seat_number'];

            if (isset($seen[$key])) {
                $errors["seats.{$index}.seat_number"][] =
                    "Ghế {$seat['seat_row']}{$seat['seat_number']} bị trùng lặp trong danh sách.";
            }

            $seen[$key] = true;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Kiểm tra tất cả ghế Sweetbox/Ghế đôi trong payload bắt buộc phải đi theo từng cặp 2 ghế dính liền.
     *
     * @param array $seats
     * @throws ValidationException
     */
    public function guardAgainstUnpairedSweetbox(array $seats): void
    {
        $sweetboxTypes = \App\Models\SeatType::where('name', 'LIKE', '%Sweetbox%')
            ->orWhere('name', 'LIKE', '%Couple%')
            ->orWhere('name', 'LIKE', '%đôi%')
            ->orWhere('name', 'LIKE', '%doi%')
            ->pluck('id')
            ->map('intval')
            ->toArray();

        if (empty($sweetboxTypes)) {
            return;
        }

        // Nhóm tất cả ghế theo hàng và số ghế
        $byRowAndNum = [];
        foreach ($seats as $seat) {
            $row = strtoupper(trim($seat['seat_row']));
            $num = (int)$seat['seat_number'];
            $typeId = (int)$seat['seat_type_id'];
            $byRowAndNum[$row][$num] = $typeId;
        }

        $errors = [];
        foreach ($seats as $index => $seat) {
            $typeId = (int)$seat['seat_type_id'];
            if (!in_array($typeId, $sweetboxTypes, true)) {
                continue;
            }

            $row = strtoupper(trim($seat['seat_row']));
            $num = (int)$seat['seat_number'];
            $siblingNum = ($num % 2 === 1) ? $num + 1 : $num - 1;

            $siblingTypeId = $byRowAndNum[$row][$siblingNum] ?? null;

            if ($siblingTypeId === null || !in_array($siblingTypeId, $sweetboxTypes, true)) {
                $errors["seats.{$index}.seat_number"][] = 
                    "Ghế Sweetbox {$row}{$num} phải đi kèm theo cặp 2 ghế dính liền ({$row}{$siblingNum}).";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Thực hiện đồng bộ toàn bộ sơ đồ ghế trong một DB Transaction.
     *
     * Quy trình:
     *  1. Xóa toàn bộ ghế cũ (chỉ ghế không liên kết showtime_seats active).
     *  2. Mass-insert danh sách ghế mới bằng Seat::insert() để tối ưu hiệu suất.
     *  3. Cập nhật rooms.capacity = số ghế vừa tạo.
     *
     * @param  Room   $room    Model Room đã xác thực quyền
     * @param  array  $seats   Mảng ghế đã được validate và de-duplicate
     * @return array           Thông tin kết quả
     */
    public function sync(Room $room, array $seats): array
    {
        $this->guardAgainstUnpairedSweetbox($seats);

        return DB::transaction(function () use ($room, $seats) {
            // ─── Bước 1: Xóa ghế cũ ─────────────────────────────────────────
            // Dùng DELETE trực tiếp (không qua Eloquent) để tránh N+1 khi
            // gọi destroy() từng record. Vì Seat model không có timestamps
            // nên không cần lo về SoftDeletes trên bảng seats.
            Seat::where('room_id', $room->id)->delete();

            // ─── Bước 2: Chuẩn bị payload insert ────────────────────────────
            // Seat model có $timestamps = false → không cần created_at/updated_at.
            $payload = array_map(function (array $seat) use ($room): array {
                return [
                    'room_id'      => $room->id,
                    'seat_row'     => strtoupper(trim($seat['seat_row'])),
                    'seat_number'  => (int) $seat['seat_number'],
                    'seat_type_id' => (int) $seat['seat_type_id'],
                    'status'       => $seat['status'],
                ];
            }, $seats);

            // ─── Bước 3: Mass-insert ─────────────────────────────────────────
            // insert() gửi 1 câu SQL thay vì N câu INSERT riêng lẻ.
            // Trường hợp mảng rỗng → bỏ qua insert (phòng có 0 ghế hợp lệ).
            if (!empty($payload)) {
                Seat::insert($payload);
            }

            $seatCount = count($payload);

            // ─── Bước 4: Cập nhật capacity ───────────────────────────────────
            $room->update(['capacity' => $seatCount]);

            // ─── Bước 5: Cập nhật lại sơ đồ ghế (showtime_seats) cho các suất chiếu upcoming/active ───
            $nowStr = now()->toDateTimeString();
            $upcomingShowtimes = \App\Models\Showtime::where('room_id', $room->id)
                ->whereIn('status', ['upcoming', 'active'])
                ->where(DB::raw("CONCAT(show_date, ' ', end_time)"), '>', $nowStr)
                ->get();

            if ($upcomingShowtimes->isNotEmpty()) {
                $surchargeMap = \App\Models\SeatType::all()->pluck('surcharge_price', 'id');
                $newSeats = Seat::where('room_id', $room->id)->select('id', 'seat_type_id')->get();

                foreach ($upcomingShowtimes as $showtime) {
                    // Giữ lại các ghế đã được đặt (booked) hoặc đang giữ chỗ (holding) để bảo toàn giao dịch
                    $existingBookedSeatIds = \App\Models\ShowtimeSeat::where('showtime_id', $showtime->id)
                        ->whereIn('status', ['booked', 'holding'])
                        ->pluck('seat_id')
                        ->toArray();

                    // Xóa các ghế trống (available) cũ
                    \App\Models\ShowtimeSeat::where('showtime_id', $showtime->id)
                        ->where('status', 'available')
                        ->delete();

                    // Sinh lại ghế trống (available) mới theo sơ đồ phòng vừa tạo
                    $showtimeSeatsData = [];
                    foreach ($newSeats as $seat) {
                        if (in_array($seat->id, $existingBookedSeatIds)) {
                            continue;
                        }
                        $surcharge = (int) ($surchargeMap[$seat->seat_type_id] ?? 0);
                        $showtimeSeatsData[] = [
                            'showtime_id' => $showtime->id,
                            'seat_id'     => $seat->id,
                            'user_id'     => null,
                            'status'      => 'available',
                            'price'       => $showtime->base_price + $surcharge,
                            'locked_at'   => null,
                            'expires_at'  => null,
                        ];
                    }

                    if (!empty($showtimeSeatsData)) {
                        \App\Models\ShowtimeSeat::insert($showtimeSeatsData);
                    }
                }
            }

            return [
                'room_id'    => $room->id,
                'room_name'  => $room->room_name,
                'seat_count' => $seatCount,
            ];
        });
    }
}
