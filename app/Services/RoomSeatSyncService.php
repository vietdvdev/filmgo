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
     * Kiểm tra xem phòng chiếu có ghế nào đang liên kết với suất chiếu SẮP DIỄN RA
     * (showtime_seats.status IN ['holding','booked'] VÀ showtimes.start_time > NOW()).
     * Chỉ chặn khi còn giao dịch đang hoạt động, không chặn dữ liệu lịch sử đã kết thúc.
     *
     * @param  int  $roomId
     * @throws \Illuminate\Validation\ValidationException
     */
    public function guardAgainstActiveBookings(int $roomId): void
    {
        // BUG-01 FIX: So sánh DATETIME đầy đủ thay vì chỉ so sánh TIME với DATETIME.
        // CONCAT(show_date, ' ', start_time) tạo ra chuỗi 'YYYY-MM-DD HH:MM:SS'
        // để MySQL so sánh chính xác với NOW() (cũng là DATETIME).
        // Trước đây: start_time > NOW() → sai kiểu dữ liệu, kết quả không nhất quán.
        $hasActive = DB::table('showtime_seats')
            ->join('seats', 'seats.id', '=', 'showtime_seats.seat_id')
            ->join('showtimes', 'showtimes.id', '=', 'showtime_seats.showtime_id')
            ->where('seats.room_id', $roomId)
            ->whereIn('showtime_seats.status', ['holding', 'booked'])
            ->where(
                DB::raw("CONCAT(showtimes.show_date, ' ', showtimes.start_time)"),
                '>',
                now()->toDateTimeString()
            )
            ->exists();

        if ($hasActive) {
            throw ValidationException::withMessages([
                'seats' => [
                    'Không thể đồng bộ sơ đồ ghế vì một số ghế đang được giữ chỗ hoặc đã được đặt vé cho suất chiếu sắp tới. '
                    . 'Vui lòng hoàn tất các giao dịch hiện tại trước khi thay đổi cấu hình.',
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

            return [
                'room_id'    => $room->id,
                'room_name'  => $room->room_name,
                'seat_count' => $seatCount,
            ];
        });
    }
}
