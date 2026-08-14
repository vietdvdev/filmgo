<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\Movie;
use App\Models\Promotion;
use App\Models\Room;
use App\Models\Showtime;
use App\Services\ComboOrderService;
use App\Services\CounterBookingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function __construct(
        protected CounterBookingService $counterService,
        protected ComboOrderService $comboOrderService
    ) {}

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
        $combos   = Combo::with('items')->where('status', 'active')->latest()->get();

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
        $search   = trim($request->input('search', '')); 

        $rooms = Room::where('cinema_id', $cinemaId)->pluck('id');

        if ($rooms->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $includeEnded = $request->boolean('include_ended');
        $currentTime  = now()->toTimeString();

        // Mặc định ẩn các suất chiếu đã kết thúc trong ngày hôm nay ngoại trừ khi include_ended=true
        $movies = Movie::whereHas('showtimes', function ($q) use ($rooms, $date, $includeEnded, $currentTime) {
            $q->whereIn('room_id', $rooms)
              ->whereDate('show_date', $date)
              ->whereNotIn('status', ['cancelled']);

            if (!$includeEnded && $date === today()->toDateString()) {
                $q->where('end_time', '>', $currentTime);
            }
        })
        ->when($search !== '', function ($query) use ($search, $rooms, $date, $includeEnded, $currentTime) {
            $query->where(function ($q) use ($search, $rooms, $date, $includeEnded, $currentTime) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('showtimes', function ($q2) use ($rooms, $date, $search, $includeEnded, $currentTime) {
                      $q2->whereIn('room_id', $rooms)
                         ->whereDate('show_date', $date)
                         ->whereNotIn('status', ['cancelled']);

                      if (!$includeEnded && $date === today()->toDateString()) {
                          $q2->where('end_time', '>', $currentTime);
                      }

                      $q2->where(function ($q3) use ($search) {
                             $q3->where('start_time', 'like', "%{$search}%")
                                ->orWhere('end_time', 'like', "%{$search}%")
                                ->orWhereHas('room', function ($q4) use ($search) {
                                     $q4->where('room_name', 'like', "%{$search}%");
                                });
                         });
                  });
            });
        })
        ->with(['showtimes' => function ($q) use ($rooms, $date, $includeEnded, $currentTime) {
            $q->whereIn('room_id', $rooms)
              ->whereDate('show_date', $date)
              ->whereNotIn('status', ['cancelled']);

            if (!$includeEnded && $date === today()->toDateString()) {
                $q->where('end_time', '>', $currentTime);
            }

            $q->with('room:id,room_name,room_type')
              ->orderBy('start_time');
        }])
        ->get(['id', 'title', 'poster', 'duration', 'age_limit']);

        $result = $movies->map(fn($movie) => [
            'id'        => $movie->id,
            'title'     => $movie->title,
            'poster'    => $movie->poster,
            'duration'  => $movie->duration,
            'age_limit' => $movie->age_limit,
            'showtimes' => $movie->showtimes
                ->map(fn($s) => [
                    'id'         => $s->id,
                    'start_time' => substr($s->start_time, 0, 5),
                    'end_time'   => substr($s->end_time, 0, 5),
                    'room_name'  => $s->room->room_name,
                    'room_type'  => $s->room->room_type,
                    'base_price' => $s->base_price,
                    'status'     => $s->status,
                ])->values(),
        ])->filter(fn($m) => $m['showtimes']->isNotEmpty())->values();

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

        /**
         * Dùng cột used_count (đã đánh index) thay vì gọi bookings()->count().
         * Tránh thêm 1 COUNT(*) JOIN query mỗi lần kiểm tra voucher tại POS.
         */
        if ($promotion->usage_limit !== null && $promotion->used_count >= $promotion->usage_limit) {
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
    // PHẦN 2B: API F&B — Bán Combo/Đồ Ăn Không Cần Suất Chiếu
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * API: Lấy danh sách đồ ăn/uống lẻ từng món (ComboItem).
     * GET /staff/pos/api/combo-items
     */
    public function apiGetComboItems(): JsonResponse
    {
        $this->authorizeStaff();

        $items = ComboItem::where('status', 'active')
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'unit', 'price'])
            ->groupBy('type')
            ->map(fn($group, $type) => [
                'type'  => $type ?: 'Khác',
                'items' => $group->map(fn($i) => [
                    'id'    => $i->id,
                    'name'  => $i->name,
                    'unit'  => $i->unit,
                    'price' => $i->price,
                ])->values(),
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    /**
     * API: Checkout đơn F&B tại quầy (không cần suất chiếu/ghế).
     * POST /staff/pos/api/checkout-fnb
     *
     * Body:
     * {
     *   "combos"         : {"3": 2},           // Combo gói: {id: qty}
     *   "combo_items"    : {"5": 1, "7": 2},   // Đồ lẻ: {id: qty}
     *   "payment_method" : "cash",
     *   "customer_phone" : "0901234567",         // Tuỳ chọn
     *   "voucher_code"   : "SALE10"             // Tuỳ chọn
     * }
     */
    public function apiCheckoutFnb(Request $request): JsonResponse
    {
        $this->authorizeStaff();

        // ── Validate ──────────────────────────────────────────────────────
        $validated = $request->validate([
            'combos'           => 'nullable|array',
            'combos.*'         => 'integer|min:0',
            'combo_items'      => 'nullable|array',
            'combo_items.*'    => 'integer|min:0',
            'payment_method'   => 'required|in:cash,transfer',
            'customer_phone'   => 'nullable|string|max:20',
            'voucher_code'     => 'nullable|string|max:50',
        ]);

        try {
            $booking = $this->comboOrderService->createCounterFnbOrder(
                staffId:        Auth::id(),
                combosData:     $validated['combos'] ?? [],
                comboItemsData: $validated['combo_items'] ?? [],
                paymentMethod:  $validated['payment_method'],
                customerPhone:  $validated['customer_phone'] ?? null,
                voucherCode:    $validated['voucher_code'] ?? null,
                cinemaId:       $this->getCinemaId(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Bán F&B thành công!',
                'booking' => $this->formatFnbReceiptData($booking),
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
            'booking_id'      => $booking->id,
            'booking_code'    => $booking->booking_code,
            'total_amount'    => $booking->total_amount,    // Giá gốc (trước giảm)
            'discount_amount' => $booking->discount_amount,
            'final_total'     => $booking->final_total,     // Số tiền thực thu
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
     * Định dạng dữ liệu receipt cho đơn F&B (không có vé/ghế).
     */
    private function formatFnbReceiptData(Booking $booking): array
    {
        return [
            'booking_id'      => $booking->id,
            'booking_code'    => $booking->booking_code,
            'total_amount'    => $booking->total_amount,
            'discount_amount' => $booking->discount_amount,
            'final_total'     => $booking->final_total,
            'payment_method'  => $booking->payments->first()?->payment_method,
            'combos' => $booking->combos->map(fn($c) => [
                'name'     => $c->combo_name,
                'quantity' => $c->pivot->quantity,
                'subtotal' => $c->pivot->subtotal,
            ])->toArray(),
            'combo_items' => $booking->comboItems->map(fn($ci) => [
                'name'       => $ci->comboItem?->name ?? 'Món ăn',
                'unit'       => $ci->comboItem?->unit,
                'quantity'   => $ci->quantity,
                'unit_price' => $ci->unit_price,
                'subtotal'   => $ci->subtotal,
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
