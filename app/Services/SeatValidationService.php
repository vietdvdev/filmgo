<?php

namespace App\Services;

use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeatValidationService
{
    // Các trạng thái không được phép chọn
    const UNAVAILABLE_STATUSES = ['occupied', 'booked', 'maintenance', 'disabled'];

    /**
     * Validate toàn bộ quy tắc trước khi cho phép sang bước tiếp theo.
     * Trả về ['valid' => true] hoặc ['valid' => false, 'message' => '...']
     */
    public function validate(int $showtimeId, array $showtimeSeatIds): array
    {
        // Rule 16: Phải đăng nhập
        if (!Auth::check()) {
            return $this->fail('Vui lòng đăng nhập để đặt vé.');
        }

        // Rule 11 & 12: seat_ids phải là mảng số nguyên hợp lệ
        foreach ($showtimeSeatIds as $id) {
            if (!is_numeric($id) || intval($id) <= 0) {
                return $this->fail('Dữ liệu ghế không hợp lệ.');
            }
        }

        // Rule 2: Giới hạn 10 ghế
        if (count($showtimeSeatIds) > 10) {
            return $this->fail('Bạn chỉ được chọn tối đa 10 ghế.');
        }

        if (count($showtimeSeatIds) === 0) {
            return $this->fail('Vui lòng chọn ít nhất một ghế.');
        }

        // Lấy suất chiếu
        $showtime = Showtime::find($showtimeId);
        if (!$showtime) {
            return $this->fail('Suất chiếu không tồn tại.');
        }

        // Rule 17: Không đặt suất đã bắt đầu
        $startDateTime = \Carbon\Carbon::parse($showtime->show_date->format('Y-m-d') . ' ' . $showtime->start_time);
        if ($startDateTime->isPast()) {
            return $this->fail('Suất chiếu này đã bắt đầu, không thể đặt vé.');
        }

        // Rule 18: Không đặt suất đã kết thúc
        if ($showtime->end_time) {
            $endDateTime = \Carbon\Carbon::parse($showtime->show_date->format('Y-m-d') . ' ' . $showtime->end_time);
            if ($endDateTime->isPast()) {
                return $this->fail('Suất chiếu này đã kết thúc.');
            }
        }

        // Rule 19: Không đặt suất đã khóa bán
        if (in_array($showtime->status, ['closed', 'cancelled'])) {
            return $this->fail('Suất chiếu này đã đóng bán vé.');
        }

        // Tự động giải phóng ghế hết hạn giữ (Rule 8 & 25)
        $this->releaseExpiredSeats($showtimeId);

        // Lấy toàn bộ ghế của suất chiếu này (dùng để kiểm tra single seat)
        $allSeats = ShowtimeSeat::with('seat')
            ->where('showtime_id', $showtimeId)
            ->get()
            ->keyBy('id');

        // Rule 9 & 12: Kiểm tra tất cả seat_id phải thuộc đúng suất chiếu này
        foreach ($showtimeSeatIds as $id) {
            if (!$allSeats->has($id)) {
                return $this->fail('Ghế không thuộc suất chiếu này hoặc không tồn tại.');
            }
        }

        // Rule 10: Kiểm tra tất cả ghế thuộc cùng phòng
        $roomIds = $allSeats->only($showtimeSeatIds)->map(fn($ss) => $ss->seat->room_id)->unique();
        if ($roomIds->count() > 1) {
            return $this->fail('Các ghế phải thuộc cùng một phòng chiếu.');
        }

        $userId = Auth::id();

        // Rule 1, 7, 13, 14, 15: Kiểm tra trạng thái từng ghế
        foreach ($showtimeSeatIds as $id) {
            $ss = $allSeats->get($id);
            $seatLabel = $ss->seat->seat_row . $ss->seat->seat_number;

            // Ghế đang bị người khác giữ còn hạn (Rule 7)
            if ($ss->status === 'locked' && $ss->user_id !== $userId && $ss->expires_at && $ss->expires_at->isFuture()) {
                return $this->fail("Ghế {$seatLabel} đang được người khác giữ.");
            }

            // Ghế holding của chính user → cho phép (Rule 28)
            if ($ss->status === 'holding' && $ss->user_id === $userId) {
                continue;
            }

            if (in_array($ss->status, self::UNAVAILABLE_STATUSES)) {
                $msg = match ($ss->status) {
                    'occupied', 'booked' => "Ghế {$seatLabel} đã được đặt.",
                    'maintenance'        => "Ghế {$seatLabel} đang bảo trì.",
                    'disabled'           => "Ghế {$seatLabel} đã bị khóa.",
                    default              => "Ghế {$seatLabel} hiện không khả dụng.",
                };
                return $this->fail($msg);
            }
        }

        // Rule 3, 4, 5, 6: Kiểm tra single seat theo từng hàng
        $singleSeatError = $this->validateSingleSeatRule($allSeats, $showtimeSeatIds, $userId);
        if ($singleSeatError) {
            return $this->fail($singleSeatError);
        }

        // Kiểm tra quy tắc ghế đôi Sweetbox
        $sweetboxError = $this->validateSweetboxRule($allSeats, $showtimeSeatIds);
        if ($sweetboxError) {
            return $this->fail($sweetboxError);
        }

        return ['valid' => true];
    }

    /**
     * Thuật toán phát hiện ghế cô đơn (Single Seat Rule) — giống CGV.
     *
     * Logic: Với mỗi hàng, xây dựng mảng trạng thái ghế:
     *   - 'X' = không khả dụng (đã bán / đang giữ bởi người khác)
     *   - 'S' = khách đang chọn
     *   - 'O' = trống (available)
     *
     * Sau đó quét từng ghế trống 'O', nếu cả hai bên đều là 'X' hoặc 'S' hoặc là biên → ghế cô đơn.
     */
    public function validateSingleSeatRule($allSeats, array $selectedIds, int $userId): ?string
    {
        // Nhóm ghế theo hàng
        $byRow = [];
        foreach ($allSeats as $ss) {
            $row = $ss->seat->seat_row;
            $byRow[$row][] = $ss;
        }

        $selectedSet = array_flip($selectedIds);

        foreach ($byRow as $row => $seats) {
            // Xây dựng mảng trạng thái với key là seat_number để đảm bảo đúng vị trí vật lý
            $states = [];
            foreach ($seats as $ss) {
                $seatNumber = $ss->seat->seat_number;
                $isSelected = isset($selectedSet[$ss->id]);
                $isUnavailable = in_array($ss->status, self::UNAVAILABLE_STATUSES)
                    || ($ss->status === 'locked' && $ss->user_id !== $userId && $ss->expires_at?->isFuture())
                    || ($ss->status === 'holding' && $ss->user_id !== $userId);

                if ($isSelected) {
                    $states[$seatNumber] = 'S'; // Đang chọn
                } elseif ($isUnavailable) {
                    $states[$seatNumber] = 'X'; // Không khả dụng
                } else {
                    $states[$seatNumber] = 'O'; // Trống
                }
            }

            $seatNumbers = array_keys($states);
            sort($seatNumbers);

            // Quét từng vị trí tìm ghế trống 'O'
            foreach ($seatNumbers as $number) {
                if ($states[$number] !== 'O') {
                    continue;
                }

                $leftExists  = isset($states[$number - 1]);
                $rightExists = isset($states[$number + 1]);

                if (!$leftExists && !$rightExists) {
                    // Ghế đứng một mình (hàng chỉ có 1 ghế) → bỏ qua
                    continue;
                }

                // ── GHẾ Ở BIÊN TRÁI (góc đầu hàng, không có hàng xóm bên trái) ──
                // Ví dụ: ghế số 1. Nếu ghế số 2 bên cạnh là 'S' (do user chọn),
                // thì ghế 1 bị bỏ trống cô đơn ở góc → KHÔNG được phép.
                if (!$leftExists && $rightExists) {
                    if ($states[$number + 1] === 'S') {
                        return "Lựa chọn của bạn bỏ trống ghế góc cô đơn ở đầu hàng {$row} (ghế số {$number}). Vui lòng chọn từ ghế đầu hàng hoặc chọn liên tiếp.";
                    }
                    continue;
                }

                // ── GHẾ Ở BIÊN PHẢI (góc cuối hàng, không có hàng xóm bên phải) ──
                // Ví dụ: ghế số 10. Nếu ghế số 9 bên cạnh là 'S' (do user chọn),
                // thì ghế 10 bị bỏ trống cô đơn ở góc → KHÔNG được phép.
                if ($leftExists && !$rightExists) {
                    if ($states[$number - 1] === 'S') {
                        return "Lựa chọn của bạn bỏ trống ghế góc cô đơn ở cuối hàng {$row} (ghế số {$number}). Vui lòng chọn đến ghế cuối hàng hoặc chọn liên tiếp.";
                    }
                    continue;
                }

                // ── GHẾ Ở GIỮA HÀNG ──
                $leftBlocked  = ($states[$number - 1] === 'X' || $states[$number - 1] === 'S');
                $rightBlocked = ($states[$number + 1] === 'X' || $states[$number + 1] === 'S');

                if ($leftBlocked && $rightBlocked) {
                    // Chỉ báo lỗi nếu ít nhất một bên là 'S' (do CHÍNH user hiện tại gây ra)
                    if ($states[$number - 1] === 'S' || $states[$number + 1] === 'S') {
                        return "Lựa chọn của bạn tạo ra ghế trống cô đơn ở hàng {$row}. Vui lòng chọn lại để không bỏ trống 1 ghế đơn lẻ.";
                    }
                }
            }
        }

        return null;
    }

    /**
     * Kiểm tra quy tắc ghế đôi Sweetbox (phải chọn cả cặp).
     */
    public function validateSweetboxRule($allSeats, array $selectedIds): ?string
    {
        $selectedSeats = $allSeats->only($selectedIds);
        $sweetboxSeats = $selectedSeats->filter(function ($ss) {
            return $ss->seat->seatType->name === 'Sweetbox';
        });

        if ($sweetboxSeats->isEmpty()) {
            return null;
        }

        // Nhóm tất cả ghế theo hàng và số ghế để tra cứu nhanh
        $seatsByRowAndNumber = [];
        foreach ($allSeats as $ss) {
            $seatsByRowAndNumber[$ss->seat->seat_row][$ss->seat->seat_number] = $ss;
        }

        $selectedSeatIds = $selectedSeats->pluck('id')->toArray();

        foreach ($sweetboxSeats as $ss) {
            $row = $ss->seat->seat_row;
            $number = $ss->seat->seat_number;
            $siblingNumber = ($number % 2 === 1) ? $number + 1 : $number - 1;

            $siblingSeat = $seatsByRowAndNumber[$row][$siblingNumber] ?? null;
            if (!$siblingSeat) {
                return "Ghế đôi Sweetbox {$row}{$number} không có ghế cùng cặp hợp lệ.";
            }

            if (!in_array($siblingSeat->id, $selectedSeatIds)) {
                return "Ghế đôi Sweetbox {$row}{$number} và {$row}{$siblingNumber} phải được chọn cùng nhau.";
            }
        }

        return null;
    }

    /**
     * Tự động giải phóng ghế hết hạn giữ (Rule 8, 25, 27, 29).
     */
    public function releaseExpiredSeats(int $showtimeId): void
    {
        ShowtimeSeat::where('showtime_id', $showtimeId)
            ->whereIn('status', ['holding', 'locked'])
            ->where('expires_at', '<', now())
            ->update([
                'status'     => 'available',
                'user_id'    => null,
                'locked_at'  => null,
                'expires_at' => null,
            ]);
    }

    /**
     * Khóa ghế sau khi khách chọn (Rule 26).
     * Dùng transaction + lockForUpdate để chống race condition (Rule 20, 21).
     */
    public function lockSeats(int $showtimeId, array $showtimeSeatIds, int $userId): array
    {
        return DB::transaction(function () use ($showtimeId, $showtimeSeatIds, $userId) {
            $seats = ShowtimeSeat::with('seat')
                ->where('showtime_id', $showtimeId)
                ->whereIn('id', $showtimeSeatIds)
                ->lockForUpdate()
                ->get();

            foreach ($seats as $ss) {
                // Cho phép nếu chính user đang giữ (Rule 28)
                if (($ss->status === 'holding' || $ss->status === 'locked') && $ss->user_id === $userId) {
                    continue;
                }

                if ($ss->status !== 'available') {
                    return [
                        'success' => false,
                        'message' => 'Ghế ' . $ss->seat->seat_row . $ss->seat->seat_number . ' vừa được người khác đặt.',
                    ];
                }
            }

            $expiresAt = now()->addMinutes(15);

            ShowtimeSeat::where('showtime_id', $showtimeId)
                ->whereIn('id', $showtimeSeatIds)
                ->update([
                    'status'     => 'holding',
                    'user_id'    => $userId,
                    'locked_at'  => now(),
                    'expires_at' => $expiresAt,
                ]);

            return ['success' => true, 'expires_at' => $expiresAt];
        });
    }

    private function fail(string $message): array
    {
        return ['valid' => false, 'message' => $message];
    }
}
