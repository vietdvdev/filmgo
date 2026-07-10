<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Combo;
use App\Models\Movie;
use App\Models\Promotion;
use App\Models\Room;
use App\Models\Showtime;
use App\Services\CounterBookingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function __construct(protected CounterBookingService $counterService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // PHẦN 1: Load giao diện POS chính
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hiển thị trang POS một màn hình duy nhất.
     * Dữ liệu ban đầu được nhúng vào view để tránh request thừa.
     */
    public function index(): \Illuminate\View\View
    {
        $this->authorizeStaff();

        $cinemaId = $this->getCinemaId();
        $combos   = Combo::where('status', 'active')->latest()->get(['id', 'combo_name', 'price', 'image', 'description']);

        return view('staff.pos.index', compact('cinemaId', 'combos'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PHẦN 2: API Endpoints (JSON)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * API: Lấy danh sách suất chiếu theo ngày.
     * GET /staff/pos/api/showtimes?date=2026-07-10
     */
    public function apiGetShowtimes(Request $request): JsonResponse
    {
        $this->authorizeStaff();

        $cinemaId = $this->getCinemaId();
        $date     = $request->input('date', today()->toDateString());

        $rooms = Room::where('cinema_id', $cinemaId)->pluck('id');

        // Lấy phim đang chiếu hôm nay tại rạp, gộp theo phim (để hiện danh sách phim trước)
        $movies = Movie::whereHas('showtimes', function ($q) use ($rooms, $date) {
            $q->whereIn('room_id', $rooms)
              ->whereDate('show_date', $date)
              ->whereNotIn('status', ['cancelled', 'finished']);
        })
        ->with(['showtimes' => function ($q) use ($rooms, $date) {
            $q->whereIn('room_id', $rooms)
              ->whereDate('show_date', $date)
              ->whereNotIn('status', ['cancelled', 'finished'])
              ->with('room:id,room_name,room_type')
              ->orderBy('start_time');
        }])
        ->get(['id', 'title', 'poster', 'duration', 'age_limit']);

        // Định dạng lại để frontend dễ tiêu thụ
        $result = $movies->map(fn($movie) => [
            'id'        => $movie->id,
            'title'     => $movie->title,
            'poster'    => $movie->poster,
            'duration'  => $movie->duration,
            'age_limit' => $movie->age_limit,
            'showtimes' => $movie->showtimes->map(fn($s) => [
                'id'         => $s->id,
                'start_time' => substr($s->start_time, 0, 5),
                'end_time'   => substr($s->end_time, 0, 5),
                'room_name'  => $s->room->room_name,
                'room_type'  => $s->room->room_type,
                'base_price' => $s->base_price,
                'status'     => $s->status,
            ]),
        ]);

        return response()->json(['data' => $result]);
    }

    /**
     * API: Lấy sơ đồ ghế real-time của một suất chiếu.
     * GET /staff/pos/api/seat-map/{showtime_id}
     */
    public function apiGetSeatMap(int $showtimeId): JsonResponse
    {
        $this->authorizeStaff();

        try {
            $data = $this->counterService->getSeatMapData($showtimeId);
            return response()->json(['data' => $data]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * API: Xác minh mã voucher.
     * POST /staff/pos/api/voucher
     */
    public function apiCheckVoucher(Request $request): JsonResponse
    {
        $this->authorizeStaff();

        $code = strtoupper(trim($request->input('code', '')));
        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'Vui lòng nhập mã.'], 422);
        }

        $promotion = Promotion::where('code', $code)->first();
        $now       = now();

        if (!$promotion
            || $promotion->status !== 'active'
            || ($promotion->start_date && $now->lt($promotion->start_date))
            || ($promotion->end_date   && $now->gt($promotion->end_date))
        ) {
            return response()->json(['valid' => false, 'message' => 'Mã không hợp lệ hoặc đã hết hạn.'], 422);
        }

        if ($promotion->quantity !== null && $promotion->bookings()->count() >= $promotion->quantity) {
            return response()->json(['valid' => false, 'message' => 'Mã đã hết lượt sử dụng.'], 422);
        }

        return response()->json([
            'valid'          => true,
            'message'        => 'Mã hợp lệ!',
            'code'           => $promotion->code,
            'discount_type'  => $promotion->discount_type,
            'discount_value' => $promotion->discount_value,
        ]);
    }

    /**
     * API: Xử lý thanh toán tại quầy (POS Checkout).
     * POST /staff/pos/api/checkout
     *
     * Body:
     * {
     *   "showtime_id"    : 5,
     *   "seat_ids"       : [101, 102],
     *   "combos"         : {"3": 2, "5": 1},
     *   "payment_method" : "cash",
     *   "customer_phone" : "0901234567",  // Tuỳ chọn
     *   "voucher_code"   : "SALE20"        // Tuỳ chọn
     * }
     */
    public function apiCheckout(Request $request): JsonResponse
    {
        $this->authorizeStaff();

        // ── Validate input ────────────────────────────────────────────────
        $validated = $request->validate([
            'showtime_id'      => 'required|integer|exists:showtimes,id',
            'seat_ids'         => 'required|array|min:1',
            'seat_ids.*'       => 'integer|exists:showtime_seats,id',
            'combos'           => 'nullable|array',
            'combos.*'         => 'integer|min:0',
            'payment_method'   => 'required|in:cash,transfer',
            'customer_phone'   => 'nullable|string|max:20',
            'voucher_code'     => 'nullable|string|max:50',
        ]);

        try {
            $booking = $this->counterService->createCounterBooking(
                staffId:        Auth::id(),
                showtimeId:     $validated['showtime_id'],
                showtimeSeatIds: $validated['seat_ids'],
                combosData:     $validated['combos'] ?? [],
                paymentMethod:  $validated['payment_method'],
                customerPhone:  $validated['customer_phone'] ?? null,
                voucherCode:    $validated['voucher_code'] ?? null,
            );

            // Trả về dữ liệu đủ để frontend in vé
            return response()->json([
                'success'  => true,
                'message'  => 'Bán vé thành công!',
                'booking'  => $this->formatBookingForReceipt($booking),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PHẦN 3: Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Định dạng dữ liệu booking để hiển thị phiếu in.
     */
    private function formatBookingForReceipt(Booking $booking): array
    {
        return [
            'booking_code'    => $booking->booking_code,
            'total_amount'    => $booking->total_amount,
            'discount_amount' => $booking->discount_amount,
            'payment_method'  => $booking->payments->first()?->payment_method,
            'showtime'        => [
                'movie'      => $booking->showtime->movie->title,
                'show_date'  => $booking->showtime->show_date,
                'start_time' => substr($booking->showtime->start_time, 0, 5),
                'room'       => $booking->showtime->room->room_name,
                'cinema'     => $booking->showtime->room->cinema->name,
            ],
            'seats' => $booking->bookingDetails->map(fn($d) => [
                'label'  => $d->showtimeSeat->seat->seat_row . $d->showtimeSeat->seat->seat_number,
                'type'   => $d->showtimeSeat->seat->seatType->name,
                'price'  => $d->price,
                'qr'     => $d->ticket?->qr_code,
            ])->toArray(),
            'combos' => $booking->combos->map(fn($c) => [
                'name'     => $c->combo_name,
                'quantity' => $c->pivot->quantity,
                'subtotal' => $c->pivot->subtotal,
            ])->toArray(),
        ];
    }

    /**
     * Kiểm tra người dùng hiện tại có role staff không.
     */
    private function authorizeStaff(): void
    {
        if (!Auth::user()?->roles()->where('name', 'staff')->exists()) {
            abort(403, 'Chức năng này chỉ dành cho nhân viên (Staff).');
        }
    }

    /**
     * Lấy cinema_id mà staff đang được phân công.
     */
    private function getCinemaId(): int
    {
        $cinema = Auth::user()->cinemas()->first();
        if (!$cinema) {
            abort(403, 'Bạn chưa được phân công vào rạp nào. Vui lòng liên hệ quản lý.');
        }
        return $cinema->id;
    }
}
