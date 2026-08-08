<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatType;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use InvalidArgumentException;

class ManagerSeatService
{
    /**
     * Xác thực quyền sở hữu phòng chiếu thuộc rạp của Manager đang đăng nhập.
     *
     * @param int $roomId
     * @param int $cinemaId
     * @return Room
     * @throws AuthorizationException|NotFoundHttpException
     */
    public function validateRoomOwnership(int $roomId, int $cinemaId): Room
    {
        $room = Room::find($roomId);

        if (!$room) {
            throw new NotFoundHttpException('Phòng chiếu không tồn tại trong hệ thống.');
        }

        // Kiểm tra phòng thuộc đúng rạp được phân công cho manager
        if ($room->cinema_id !== $cinemaId) {
            throw new AuthorizationException('Bạn không có quyền quản lý phòng chiếu của rạp khác.');
        }

        return $room;
    }

    /**
     * Lấy danh sách ghế của phòng chiếu và thông tin các loại ghế.
     *
     * @param int $roomId
     * @param int $cinemaId
     * @return array
     */
    public function getRoomSeatsAndTypes(int $roomId, int $cinemaId): array
    {
        $room = $this->validateRoomOwnership($roomId, $cinemaId);

        $seats = Seat::where('room_id', $roomId)
            ->with('seatType')
            ->get();

        // Sắp xếp theo số ghế trước, sau đó gom nhóm theo hàng và sắp xếp các hàng (A, B, C...)
        $groupedSeats = $seats->sortBy('seat_number')
            ->groupBy('seat_row')
            ->sortKeys();

        $seatTypes = SeatType::all();

        return [
            'room' => $room,
            'groupedSeats' => $groupedSeats,
            'seatTypes' => $seatTypes
        ];
    }

    /**
     * Tạo hàng loạt ghế cho một hàng.
     *
     * @param int $roomId
     * @param int $cinemaId
     * @param array $data
     * @return int Số lượng ghế đã tạo thành công
     * @throws InvalidArgumentException
     */
    public function bulkStoreSeats(int $roomId, int $cinemaId, array $data): int
    {
        $this->validateRoomOwnership($roomId, $cinemaId);

        $seatRow = strtoupper(trim($data['seat_row'] ?? ''));
        $startNumber = (int)($data['start_number'] ?? 0);
        $endNumber = (int)($data['end_number'] ?? 0);
        $seatTypeId = (int)($data['seat_type_id'] ?? 0);

        if (empty($seatRow) || strlen($seatRow) > 5) {
            throw new InvalidArgumentException('Ký hiệu hàng ghế không hợp lệ (ví dụ: A, B, AA).');
        }

        if ($startNumber <= 0 || $endNumber <= 0 || $startNumber > $endNumber) {
            throw new InvalidArgumentException('Khoảng số ghế bắt đầu và kết thúc không hợp lệ.');
        }

        $totalSeatsCount = $endNumber - $startNumber + 1;
        if ($totalSeatsCount > 50) {
            throw new InvalidArgumentException('Mỗi lần chỉ tạo được tối đa 50 ghế trên một hàng.');
        }

        $seatTypeExists = SeatType::where('id', $seatTypeId)->exists();
        if (!$seatTypeExists) {
            throw new InvalidArgumentException('Loại ghế được chọn không tồn tại.');
        }

        // BUG-06 FIX: Thay vì hardcode ID = 3 để phát hiện ghế Sweetbox/Couple,
        // tra cứu động theo tên để tránh lỗi khi seed DB với thứ tự ID khác nhau.
        // Kiểm tra loại ghế: Nếu là ghế Đôi / Sweetbox thì bắt buộc tổng số lượng
        // ghế sinh ra phải là SỐ CHẴN (% 2 === 0) để xếp đủ cặp.
        $sweetboxType      = SeatType::where('name', 'LIKE', '%Sweetbox%')
            ->orWhere('name', 'LIKE', '%Couple%')
            ->first();
        $isCoupleSeatType  = ($sweetboxType && $seatTypeId === $sweetboxType->id)
            || isset($data['couple_seats_count']);

        if ($isCoupleSeatType) {
            $coupleSeatsCount = isset($data['couple_seats_count'])
                ? (int)$data['couple_seats_count']
                : $totalSeatsCount;

            // Logic Modulo: $coupleSeatsCount % 2 !== 0 nghĩa là số lẻ, không thể ghép cặp hoàn chỉnh
            if ($coupleSeatsCount % 2 !== 0) {
                throw new InvalidArgumentException('Số lượng ghế đôi bắt buộc phải là số chẵn.');
            }
        }

        // Lấy danh sách các số ghế đã tồn tại cho hàng ghế này
        $existingNumbers = Seat::where('room_id', $roomId)
            ->where('seat_row', $seatRow)
            ->whereBetween('seat_number', [$startNumber, $endNumber])
            ->pluck('seat_number')
            ->toArray();

        $seatsToInsert = [];
        for ($num = $startNumber; $num <= $endNumber; $num++) {
            if (!in_array($num, $existingNumbers)) {
                // Mỗi ghế cá nhân (Individual Seat) vẫn lưu thành 1 dòng trong database bảng `seats`
                // Việc ghép cặp được tính theo số thứ tự: 
                // Ghế lẻ ($num % 2 !== 0) và ghế chẵn ($num % 2 === 0) tiếp theo tạo thành 1 cặp sofa Sweetbox
                $seatsToInsert[] = [
                    'room_id'      => $roomId,
                    'seat_type_id' => $seatTypeId,
                    'seat_row'     => $seatRow,
                    'seat_number'  => $num,
                    'status'       => 'active',
                ];
            }
        }

        if (empty($seatsToInsert)) {
            throw new InvalidArgumentException("Tất cả các ghế từ {$seatRow}{$startNumber} đến {$seatRow}{$endNumber} đều đã tồn tại.");
        }

        DB::transaction(function () use ($seatsToInsert) {
            Seat::insert($seatsToInsert);
        });

        return count($seatsToInsert);
    }

    /**
     * Cập nhật thông tin của một ghế.
     *
     * @param int $roomId
     * @param int $seatId
     * @param int $cinemaId
     * @param array $data
     * @return Seat
     * @throws InvalidArgumentException
     */
    public function updateSeat(int $roomId, int $seatId, int $cinemaId, array $data): Seat
    {
        $this->validateRoomOwnership($roomId, $cinemaId);

        $seat = Seat::where('id', $seatId)
            ->where('room_id', $roomId)
            ->first();

        if (!$seat) {
            throw new NotFoundHttpException('Ghế không tồn tại trong phòng chiếu này.');
        }

        if (isset($data['seat_type_id'])) {
            $seatTypeExists = SeatType::where('id', (int)$data['seat_type_id'])->exists();
            if (!$seatTypeExists) {
                throw new InvalidArgumentException('Loại ghế không hợp lệ.');
            }
            $seat->seat_type_id = (int)$data['seat_type_id'];
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], ['active', 'maintenance'])) {
                throw new InvalidArgumentException('Trạng thái ghế không hợp lệ.');
            }
            $seat->status = $data['status'];
        }

        $seat->save();

        return $seat->load('seatType');
    }

    /**
     * Xóa một ghế vật lý cụ thể. Nếu là ghế Sweetbox (Ghế đôi), tự động xóa cả 2 ghế trong cặp.
     *
     * @param int $roomId
     * @param int $seatId
     * @param int $cinemaId
     * @return bool
     */
    public function deleteSeat(int $roomId, int $seatId, int $cinemaId): bool
    {
        $this->validateRoomOwnership($roomId, $cinemaId);

        $seat = Seat::where('id', $seatId)
            ->where('room_id', $roomId)
            ->with('seatType')
            ->first();

        if (!$seat) {
            throw new NotFoundHttpException('Ghế không tồn tại hoặc không thuộc phòng chiếu này.');
        }

        // Kiểm tra xem ghế đã có suất chiếu nào liên kết hoặc đã được đặt trước đó chưa
        $hasBookings = $seat->showtimeSeats()->exists();
        if ($hasBookings) {
            throw new InvalidArgumentException('Không thể xóa ghế này vì đã được liên kết với lịch chiếu hoặc giao dịch đặt vé.');
        }

        // Kiểm tra loại ghế Sweetbox / Ghế đôi
        $typeName = mb_strtolower($seat->seatType->name ?? '');
        $isSweetbox = str_contains($typeName, 'sweetbox') || str_contains($typeName, 'couple') || str_contains($typeName, 'đôi') || str_contains($typeName, 'doi');

        if ($isSweetbox) {
            $partnerNumber = ($seat->seat_number % 2 === 1) ? $seat->seat_number + 1 : $seat->seat_number - 1;
            $partnerSeat = Seat::where('room_id', $roomId)
                ->where('seat_row', $seat->seat_row)
                ->where('seat_number', $partnerNumber)
                ->first();

            if ($partnerSeat) {
                if ($partnerSeat->showtimeSeats()->exists()) {
                    throw new InvalidArgumentException("Không thể xóa cặp ghế Sweetbox này vì ghế {$partnerSeat->seat_row}{$partnerSeat->seat_number} đã được liên kết với lịch chiếu.");
                }

                return DB::transaction(function () use ($seat, $partnerSeat) {
                    $partnerSeat->delete();
                    return (bool)$seat->delete();
                });
            }
        }

        return (bool)$seat->delete();
    }
}
