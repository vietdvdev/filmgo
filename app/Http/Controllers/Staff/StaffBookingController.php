<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\StaffBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class StaffBookingController extends Controller
{
    /**
     * Khởi tạo Controller và tiêm phụ thuộc (Dependency Injection) StaffBookingService.
     *
     * @param StaffBookingService $staffBookingService
     */
    public function __construct(
        protected StaffBookingService $staffBookingService
    ) {}

    /**
     * Hiển thị danh sách vé đặt trong ngày dành cho Nhân viên rạp.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // 1. Xác định Rạp của nhân viên đang đăng nhập
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        // Validate ngày từ Request (mặc định lấy hôm nay)
        $request->validate([
            'date'         => ['nullable', 'date_format:Y-m-d'],
            'booking_code' => ['nullable', 'string', 'max:100'],
            'print_status' => ['nullable', 'in:printed,not_printed'],
        ]);

        $date         = $request->input('date', now()->toDateString());
        $selectedDate = $date;

        $filters = $request->only(['booking_code', 'print_status']);

        // 2. Lấy danh sách booking từ Service
        $bookings = $this->staffBookingService->getDailyBookingsByCinema($cinema->id, $date, $filters);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'cinema'  => [
                    'id'   => $cinema->id,
                    'name' => $cinema->name,
                ],
                'date'    => $date,
                'data'    => $bookings,
            ]);
        }

        return view('staff.bookings.index', compact('bookings', 'cinema', 'date', 'selectedDate', 'filters'));
    }

    /**
     * API Lấy danh sách QR Code của đơn hàng (getTicketsQR).
     *
     * Bảo mật: Kiểm tra bookingId thuộc rạp nhân viên quản lý (trả về 403 nếu sai rạp).
     * Xử lý: Sinh mã QR SVG/Base64 từ cột qr_code bằng QrCode::size(200)->generate($ticket->qr_code).
     *
     * @param int $bookingId
     * @return JsonResponse
     */
    public function getTicketsQR(int $bookingId): JsonResponse
    {
        $user   = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        // Truy vấn Booking & Kiểm tra bảo mật theo rạp
        $booking = $this->staffBookingService->getBookingForStaff($bookingId, $cinema->id);

        // Sinh dữ liệu các vé thuộc đơn hàng (kèm tên ghế đầy đủ Hàng + Số VD: A5, H1)
        $tickets = $this->staffBookingService->generateTicketsQrData($booking);

        return response()->json([
            'status'       => 'success',
            'booking_id'   => $booking->id,
            'booking_code' => $booking->booking_code,
            'cinema_name'  => $cinema->name,
            'tickets'      => $tickets,
        ]);
    }

    /**
     * API: Đánh dấu đã in vé (gọi từ POS sau khi in).
     */
    public function markPrinted(int $bookingId): \Illuminate\Http\JsonResponse
    {
        $user   = Auth::user();
        $cinema = $user?->cinemas()->first();
        if (!$cinema) abort(Response::HTTP_FORBIDDEN);

        $booking = $this->staffBookingService->getBookingForStaff($bookingId, $cinema->id);
        $booking->update(['printed_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * In vé xem phim và Phiếu nhận Bắp nước trực tiếp tại quầy (Thermal Printer 80mm & Auto Print).
     *
     * @param int $bookingId
     * @return View
     */
    public function printTickets(int $bookingId): View
    {
        $user   = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        // 1. Truy vấn Eager Loading đầy đủ quan hệ & Bảo mật đúng rạp
        $booking = $this->staffBookingService->getBookingForStaff($bookingId, $cinema->id);

        // 2. Cập nhật thời gian in vé (lần đầu hoặc in lại)
        $booking->update(['printed_at' => now()]);

        // 3. Sinh dữ liệu QR Code từng chiếc vé
        $ticketsData = $this->staffBookingService->generateTicketsQrData($booking);

        // 4. Trả về Blade View chuyên dùng cho máy in nhiệt (có sẵn window.print())
        return view('staff.bookings.print', compact('booking', 'cinema', 'ticketsData'));
    }
}
