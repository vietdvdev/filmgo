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

        $booking = $this->staffBookingService->getBookingForStaff($bookingId, $cinema->id);

        $detailIds  = request()->query('detail_ids');
        $isReprint  = !is_null($booking->printed_at); // Kiểm tra nếu đơn hàng ĐÃ ĐƯỢC IN TRƯỚC ĐÓ

        // QUY ĐỊNH BẢO MẬT: Không cho in vé đối với các suất chiếu đã hết hạn
        if ($booking->showtime && $booking->showtime->isExpired()) {
            abort(Response::HTTP_BAD_REQUEST, 'Không thể in vé cho suất chiếu đã kết thúc hoặc hết hạn.');
        }

        // Cập nhật thời gian in vé
        $booking->update(['printed_at' => now()]);

        // QUY ĐỊNH BẢO MẬT: Nếu là IN LẠI ($isReprint = true) hoặc in ghế riêng lẻ ($detailIds)
        // -> TUYỆT ĐỐI KHÔNG IN KÈM PHIẾU BẮP NƯỚC (includeFnb = false)
        $includeFnb = !$isReprint && !$detailIds;

        $ticketsData = $this->staffBookingService->generateTicketsQrData($booking, $detailIds ?: null);

        return view('staff.bookings.print', compact('booking', 'cinema', 'ticketsData', 'includeFnb', 'isReprint'));
    }

    /**
     * Hiển thị trang Quét mã QR liên kết Camera để in vé cho khách hàng.
     */
    public function scanQrView(): View
    {
        $user = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        return view('staff.bookings.scan-qr', compact('cinema'));
    }

    /**
     * Tra cứu thông tin đơn hàng / vé từ chuỗi mã QR vừa quét hoặc nhập tay.
     */
    public function lookupQrCode(Request $request): JsonResponse
    {
        $user   = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chưa được phân công làm việc tại rạp nào.'], 403);
        }

        $rawCode = trim((string) $request->input('code', ''));
        if (empty($rawCode)) {
            return response()->json(['status' => 'error', 'message' => 'Mã QR / Mã đơn hàng không được để trống.'], 422);
        }

        $cleanCode = ltrim($rawCode, '#');

        // Tìm đơn theo booking_code
        $booking = \App\Models\Booking::with([
            'showtime.movie',
            'showtime.room',
            'user:id,full_name,phone,email',
            'bookingDetails.showtimeSeat.seat',
            'bookingDetails.ticket',
            'combos',
            'comboItems.comboItem',
            'payments'
        ])->where(function($q) use ($cleanCode, $rawCode) {
            $q->where('booking_code', $cleanCode)
              ->orWhere('booking_code', $rawCode);
        })->first();

        // Tìm theo ticket qr_code
        if (!$booking) {
            $ticket = \App\Models\Ticket::where('qr_code', $cleanCode)
                ->orWhere('qr_code', $rawCode)
                ->first();

            if ($ticket && $ticket->bookingDetail) {
                $booking = \App\Models\Booking::with([
                    'showtime.movie',
                    'showtime.room',
                    'user:id,full_name,phone,email',
                    'bookingDetails.showtimeSeat.seat',
                    'bookingDetails.ticket',
                    'combos',
                    'comboItems.comboItem',
                    'payments'
                ])->find($ticket->bookingDetail->booking_id);
            }
        }

        // Tìm theo token giải mã AES-256 (nếu có)
        if (!$booking) {
            try {
                $qrService = app(\App\Services\TicketQrCodeService::class);
                $payload = $qrService->decryptPayload($cleanCode);
                if (!empty($payload['order_id'])) {
                    $booking = \App\Models\Booking::with([
                        'showtime.movie',
                        'showtime.room',
                        'user:id,full_name,phone,email',
                        'bookingDetails.showtimeSeat.seat',
                        'bookingDetails.ticket',
                        'combos',
                        'comboItems.comboItem',
                        'payments'
                    ])->where('booking_code', $payload['order_id'])->first();
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (!$booking) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy đơn hàng hoặc vé phù hợp với mã: ' . $rawCode
            ], 404);
        }

        // Kiểm tra đúng rạp
        if ($booking->cinema_id && $booking->cinema_id !== $cinema->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Đơn hàng ' . $booking->booking_code . ' thuộc rạp khác, không phải rạp ' . $cinema->name
            ], 403);
        }

        $isComboOnly = ($booking->booking_type === 'combo_only' || !$booking->showtime_id);
        $isExpired = $booking->showtime ? $booking->showtime->isExpired() : false;
        $seats = $booking->bookingDetails->map(function($d) {
            $s = $d->showtimeSeat?->seat;
            return $s ? strtoupper($s->seat_row) . $s->seat_number : null;
        })->filter()->values();

        $printUrl = $isComboOnly
            ? route('staff.combo-bookings.print-receipt', $booking->id)
            : route('staff.bookings.print-tickets', $booking->id);

        return response()->json([
            'status'  => 'success',
            'booking' => [
                'id'             => $booking->id,
                'booking_code'   => $booking->booking_code,
                'booking_type'   => $booking->booking_type,
                'is_combo_only'  => $isComboOnly,
                'payment_status' => $booking->payment_status,
                'booking_status' => $booking->booking_status,
                'total_amount'   => number_format($booking->final_total ?? $booking->total_amount),
                'printed_at'     => $booking->printed_at ? $booking->printed_at->format('H:i - d/m/Y') : null,
                'is_printed'     => !is_null($booking->printed_at),
                'is_expired'     => $isExpired,
                'customer_name'  => $booking->user?->full_name ?? 'Khách vãng lai',
                'customer_phone' => $booking->user?->phone ?? '—',
                'movie_title'    => $booking->showtime?->movie?->title ?? ($isComboOnly ? 'Đơn Hàng Combo Bắp Nước' : 'Vé Xem Phim'),
                'show_date'      => $booking->showtime?->show_date?->format('d/m/Y') ?? '—',
                'show_time'      => $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '—',
                'room_name'      => $booking->showtime?->room?->room_name ?? '—',
                'seats'          => $seats,
                'combos'         => $booking->combos->map(fn($c) => [
                    'name' => $c->combo_name,
                    'qty'  => $c->pivot->quantity,
                ]),
                'combo_items'    => $booking->comboItems->map(fn($ci) => [
                    'name' => $ci->comboItem?->name ?? 'Món lẻ',
                    'qty'  => $ci->quantity,
                ]),
                'print_url'      => $printUrl,
            ]
        ]);
    }
}
