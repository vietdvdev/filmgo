<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class StaffBookingService
{
    /**
     * Truy vấn danh sách đơn đặt vé trong ngày theo Rạp phân công của nhân viên.
     * 
     * Tối ưu hiệu suất truy vấn (Eager Loading):
     * - 'user': Lấy thông tin khách hàng (họ tên, SĐT, email).
     * - 'showtime.movie': Lấy thông tin phim (tên phim, ảnh, thời lượng).
     * - 'showtime.room': Lấy thông tin phòng chiếu thuộc rạp.
     * - 'bookingDetails.showtimeSeat.seat': Lấy danh sách số ghế khách đặt.
     * - 'payments': Lấy lịch sử/trạng thái thanh toán đơn hàng.
     *
     * @param int $cinemaId ID của rạp mà nhân viên được phân công làm việc.
     * @param string|null $date Ngày chiếu dạng Y-m-d (mặc định lấy ngày hôm nay).
     * @param int $perPage Số lượng phần tử hiển thị trên một trang.
     * @return LengthAwarePaginator
     */
    public function getDailyBookingsByCinema(int $cinemaId, ?string $date = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // 1. Xác định ngày chiếu cần lọc (Nếu không truyền date -> Mặc định ngày hôm nay)
        $targetDate = $date ?? now()->toDateString();

        return Booking::query()
            // 2. Tối ưu hiệu suất: Load trước tất cả quan hệ cần thiết truyền ra View (Eager Loading)
            ->with([
                'user',
                'showtime.movie',
                'showtime.room',
                'bookingDetails.showtimeSeat.seat',
                'payments',
            ])
            // 3. Ràng buộc an toàn dữ liệu: Chỉ lấy các đơn đặt vé thuộc rạp của nhân viên
            ->whereHas('showtime.room', function ($query) use ($cinemaId) {
                $query->where('cinema_id', $cinemaId);
            })
            // 4. Lọc danh sách theo ngày chiếu của suất chiếu (show_date)
            ->whereHas('showtime', function ($query) use ($targetDate) {
                $query->whereDate('show_date', $targetDate);
            })
            // Chỉ hiển thị những đơn đã thanh toán (payment_status = 'paid' hoặc booking_status = 'confirmed')
            ->where(function ($query) {
                $query->where('bookings.payment_status', 'paid')
                      ->orWhere('bookings.booking_status', 'confirmed');
            })
            ->when(!empty($filters['booking_code']), function ($query) use ($filters) {
                $query->where('bookings.booking_code', trim($filters['booking_code']));
            })
            ->when(isset($filters['print_status']) && $filters['print_status'] !== '', function ($query) use ($filters) {
                if ($filters['print_status'] === 'printed') {
                    $query->whereNotNull('bookings.printed_at');
                } elseif ($filters['print_status'] === 'not_printed') {
                    $query->whereNull('bookings.printed_at');
                }
            })
            // 5. Sắp xếp đơn hàng mới nhất lên đầu
            ->orderBy('bookings.created_at', 'desc')
            ->orderBy('bookings.id', 'desc')
            ->select('bookings.*')
            // 6. Phân trang dữ liệu kết hợp duy trì tham số query string trên thanh địa chỉ
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Truy vấn chi tiết một Booking và kiểm tra bảo mật theo Rạp nhân viên quản lý.
     *
     * @param int $bookingId ID của đơn hàng cần tra cứu.
     * @param int $cinemaId ID của rạp nhân viên đang làm việc.
     * @return Booking
     */
    public function getBookingForStaff(int $bookingId, int $cinemaId): Booking
    {
        $booking = Booking::with([
            'user',
            'showtime.movie',
            'showtime.room.cinema',
            'bookingDetails.showtimeSeat.seat',
            'bookingDetails.ticket',
            'combos',
            'promotion',
            'payments',
        ])->findOrFail($bookingId);

        // Bảo mật: Kiểm tra xem Booking này có thuộc rạp nhân viên quản lý hay không
        if ($booking->showtime?->room?->cinema_id !== $cinemaId) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn không có quyền truy cập hoặc in vé thuộc rạp khác.');
        }

        return $booking;
    }

    public function getDailyComboBookingsByCinema(int $cinemaId, ?string $date = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $targetDate = $date ?? now()->toDateString();

        return Booking::query()
            ->with(['user', 'combos', 'comboItems.comboItem', 'bookingDetails.ticket', 'payments'])
            ->where('booking_type', 'combo_only')
            // Chỉ hiển thị những đơn đã thanh toán (payment_status = 'paid' hoặc booking_status = 'confirmed')
            ->where(function ($query) {
                $query->where('payment_status', 'paid')
                      ->orWhere('booking_status', 'confirmed');
            })
            ->where(function ($query) use ($cinemaId) {
                $query->where(function ($q) use ($cinemaId) {
                    $q->whereNotNull('cinema_id')
                      ->where('cinema_id', $cinemaId);
                })
                ->orWhereHas('staff.cinemas', function ($cinemaQuery) use ($cinemaId) {
                    $cinemaQuery->where('cinemas.id', $cinemaId);
                })
                ->orWhere(function ($q) {
                    $q->whereNull('cinema_id')
                      ->whereNull('staff_id');
                });
            })
            ->when(!empty($filters['booking_code']), function ($query) use ($filters) {
                $query->where('booking_code', trim($filters['booking_code']));
            })
            ->when(isset($filters['print_status']) && $filters['print_status'] !== '', function ($query) use ($filters) {
                if ($filters['print_status'] === 'printed') {
                    $query->whereNotNull('printed_at');
                } elseif ($filters['print_status'] === 'not_printed') {
                    $query->whereNull('printed_at');
                }
            })
            ->whereDate('created_at', $targetDate)
            // Sắp xếp đơn hàng mới nhất lên đầu
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getComboBookingForStaff(int $bookingId, int $cinemaId): Booking
    {
        $booking = Booking::with([
            'user',
            'combos',
            'comboItems.comboItem',
            'bookingDetails.ticket',
            'payments',
        ])->findOrFail($bookingId);

        $belongsToCinema = 
            ($booking->cinema_id !== null && $booking->cinema_id === $cinemaId)
            || ($booking->staff && $booking->staff->cinemas()->where('cinemas.id', $cinemaId)->exists())
            || ($booking->cinema_id === null && $booking->staff_id === null);

        if (!$belongsToCinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn không có quyền truy cập đơn combo ở rạp khác.');
        }

        return $booking;
    }

    /**
     * Tạo mảng dữ liệu QR Code cho các vé thuộc đơn hàng bằng simplesoftwareio/simple-qrcode.
     * Đảm bảo vị trí ghế luôn ghi rõ cả Hàng + Số ghế (Ví dụ: GHẾ A5, H1).
     *
     * @param Booking $booking
     * @return array
     */
    public function generateTicketsQrData(Booking $booking): array
    {
        $ticketsData = [];

        foreach ($booking->bookingDetails as $detail) {
            $seat = $detail->showtimeSeat?->seat;
            
            // Format vị trí ghế chuẩn: Kết hợp Hàng ghế (seat_row) + Số ghế (seat_number) -> Ví dụ: A5, H1
            $seatName = 'N/A';
            if ($seat) {
                $row = trim($seat->seat_row ?? '');
                $num = trim((string) ($seat->seat_number ?? ''));

                if (!empty($row) && !str_starts_with(strtoupper($num), strtoupper($row))) {
                    $seatName = strtoupper($row) . $num;
                } else {
                    $seatName = strtoupper($num);
                }
            }

            $ticket = $detail->ticket;

            $qrContent = null;
            if ($ticket && !empty($ticket->qr_code)) {
                // Sinh mã QR (SVG/Base64) kích thước 200px từ cột qr_code của vé
                if (class_exists(QrCode::class)) {
                    $qrContent = (string) QrCode::size(200)->generate($ticket->qr_code);
                } else {
                    $qrContent = $ticket->qr_code;
                }
            }

            $ticketsData[] = [
                'ticket_id' => $ticket?->id,
                'seat_name' => $seatName,
                'qr_code'   => $ticket?->qr_code,
                'qr_image'  => $qrContent,
            ];
        }

        return $ticketsData;
    }
}
