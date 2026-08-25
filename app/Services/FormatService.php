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
        $movie = Movie::query()
            ->whereKey($movieId)
            ->with(['formats' => fn ($query) => $query
                ->select('formats.id', 'formats.name', 'formats.surcharge_price')
                ->orderBy('formats.id')
            ])
            ->first();

        if (!$movie || $movie->formats->isEmpty()) {
            return new Collection();
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
            return new Collection();
        }

        return Room::where('cinema_id', $cinemaId)
            ->where('status', 'active')
            ->orderBy('room_name')
            ->get(['id', 'room_name', 'room_type', 'capacity', 'cinema_id', 'format_id'])
            ->filter(fn (Room $room) => $this->roomMatchesFormat($room, $format))
            ->values();
    }

    /**
     * Kiểm tra phòng có đúng định dạng đã cấu hình hay không.
     * Phòng cũ chưa có format_id được đối chiếu qua room_type.
     */
    public function roomMatchesFormat(Room $room, Format $format): bool
    {
        if ($room->format_id !== null) {
            return (int) $room->format_id === (int) $format->id;
        }

        $roomType = strtoupper(trim((string) $room->room_type));
        $roomType = $roomType === '4D' ? '4DX' : $roomType;

        return $roomType === strtoupper(trim($format->name));
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

        // 2. Kiểm tra Phòng chiếu có đúng định dạng đó hay không
        $format = Format::find($formatId);
        $room   = Room::find($roomId);

        if ($format && $room) {
            if (!$this->roomMatchesFormat($room, $format)) {
                $errors['room_id'] = sprintf(
                    'Phòng chiếu "%s" không được cấu hình cho định dạng %s. Vui lòng chọn phòng đúng định dạng.',
                    $room->room_name,
                    $format->name
                );
            }
        }

        return $errors;
    }
}
