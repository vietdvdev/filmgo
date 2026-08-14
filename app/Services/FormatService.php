<?php

namespace App\Services;

use App\Models\Format;
use App\Models\Movie;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class FormatService
{
    /**
     * BƯỚC 1: Lấy danh sách các Định dạng (formats) mà bộ phim hỗ trợ (thông qua movie_formats).
     *
     * @param int $movieId
     * @return Collection
     */
    public function getFormatsByMovie(int $movieId): Collection
    {
        $movie = Movie::with('formats')->find($movieId);

        if (!$movie || $movie->formats->isEmpty()) {
            return collect();
        }

        return $movie->formats;
    }

    /**
     * Lấy danh sách các kiểu phòng (room_type) đủ tiêu chuẩn chiếu định dạng này.
     *
     * Quy tắc tương thích tiêu chuẩn:
     * - Định dạng 2D: Chiếu được trên tất cả các phòng (2D, 3D, IMAX, 4DX)
     * - Định dạng 3D: Chỉ chiếu được trên phòng 3D, IMAX, 4DX (bỏ qua phòng 2D)
     * - Định dạng IMAX: Chỉ chiếu được trên phòng IMAX
     * - Định dạng 4DX: Chỉ chiếu được trên phòng 4DX
     *
     * @param string $formatName Tên định dạng (VD: '2D', '3D', 'IMAX', '4DX')
     * @return array<string>
     */
    public function getCompatibleRoomTypes(string $formatName): array
    {
        $formatUpper = strtoupper(trim($formatName));

        // Strict matching: mỗi định dạng chiếu chỉ tương thích với đúng loại phòng tương ứng.
        // VD: Phim 2D → chỉ hiện phòng 2D, Phim 3D → chỉ hiện phòng 3D, v.v.
        return match ($formatUpper) {
            '2D'    => ['2D', '3D', 'IMAX', '4DX'],
            '3D'    => ['3D', 'IMAX', '4DX'],
            'IMAX'  => ['IMAX'],
            '4DX'   => ['4DX'],
            default => [$formatUpper],
        };
    }

    /**
     * BƯỚC 2: Lọc các phòng chiếu thuộc rạp $cinemaId phù hợp với định dạng $formatId.
     *
     * @param int $cinemaId
     * @param int $formatId
     * @return Collection
     */
    public function getCompatibleRooms(int $cinemaId, int $formatId): Collection
    {
        $format = Format::find($formatId);

        if (!$format) {
            return collect();
        }

        $compatibleTypes = $this->getCompatibleRoomTypes($format->name);

        return Room::where('cinema_id', $cinemaId)
            ->where('status', 'active')
            ->whereIn('room_type', $compatibleTypes)
            ->orderBy('room_name')
            ->get(['id', 'room_name', 'room_type', 'capacity', 'cinema_id']);
    }

    /**
     * BƯỚC 3 (Validation Helper): Kiểm tra tính hợp lệ chéo giữa Phim, Định dạng và Phòng chiếu.
     *
     * @param int $movieId
     * @param int $formatId
     * @param int $roomId
     * @return array<string, string> Trả về mảng lỗi nếu có, rỗng nếu hợp lệ.
     */
    public function validateShowtimeFormatAndRoom(int $movieId, int $formatId, int $roomId): array
    {
        $errors = [];

        // Kiểm tra Định dạng có khớp với formats của bộ Phim hay không
        $movieSupportFormat = Movie::where('id', $movieId)
            ->whereHas('formats', fn($q) => $q->where('formats.id', $formatId))
            ->exists();

        if (!$movieSupportFormat) {
            $errors['format_id'] = 'Định dạng chiếu này không nằm trong danh sách hỗ trợ của bộ phim đã chọn.';
        }

        // 2. Kiểm tra Phòng chiếu có đủ tiêu chuẩn chiếu Định dạng đó hay không
        $format = Format::find($formatId);
        $room   = Room::find($roomId);

        if ($format && $room) {
            $compatibleTypes = $this->getCompatibleRoomTypes($format->name);
            if (!in_array($room->room_type, $compatibleTypes, true)) {
                $errors['room_id'] = sprintf(
                    'Phòng chiếu "%s" (Loại %s) không đủ tiêu chuẩn để chiếu định dạng %s. Vui lòng chọn phòng loại: %s.',
                    $room->room_name,
                    $room->room_type,
                    $format->name,
                    implode(', ', $compatibleTypes)
                );
            }
        }

        return $errors;
    }
}
