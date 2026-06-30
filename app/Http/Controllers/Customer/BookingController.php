<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Combo;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Step 1: Show seats configuration for selecting.
     */
    public function selectSeats($showtimeId)
    {
        $showtime = Showtime::with(['movie', 'room.cinema'])->findOrFail($showtimeId);
        
        $showtimeSeats = ShowtimeSeat::with('seat.seatType')
            ->where('showtime_id', $showtimeId)
            ->get()
            ->sortBy(function ($ss) {
                return $ss->seat->seat_row . sprintf('%03d', $ss->seat->seat_number);
            });

        // Nhóm các ghế theo hàng để vẽ sơ đồ dạng bảng lưới
        $seatsByRow = $showtimeSeats->groupBy(function ($ss) {
            return $ss->seat->seat_row;
        });

        // Lấy danh sách ghế đã chọn trước đó trong session (nếu có)
        $savedSeatIds = session()->get("booking.{$showtimeId}.seat_ids", []);

        return view('customer.bookings.select-seats', compact('showtime', 'seatsByRow', 'savedSeatIds'));
    }

    /**
     * Step 1 Processing: Save selected seat IDs to session.
     */
    public function processSeats(Request $request, $showtimeId)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer|exists:showtime_seats,id',
        ], [
            'seat_ids.required' => 'Vui lòng chọn ít nhất một vị trí ghế ngồi.',
        ]);

        // Cần đảm bảo các ghế này đều ở trạng thái available trong DB (hoặc do chính mình giữ)
        $seats = ShowtimeSeat::where('showtime_id', $showtimeId)
            ->whereIn('id', $request->seat_ids)
            ->get();

        foreach ($seats as $seat) {
            if ($seat->status !== 'available') {
                return redirect()->back()->withInput()->with('error', 'Một số ghế bạn chọn đã có người đặt trước đó. Vui lòng chọn ghế khác.');
            }
        }

        session()->put("booking.{$showtimeId}.seat_ids", $request->seat_ids);

        return redirect()->route('booking.select-combos', $showtimeId);
    }

    /**
     * Step 2: Show active food combos to upsell.
     */
    public function selectCombos($showtimeId)
    {
        $seatIds = session()->get("booking.{$showtimeId}.seat_ids");
        if (empty($seatIds)) {
            return redirect()->route('booking.select-seats', $showtimeId)->with('error', 'Vui lòng chọn ghế ngồi trước.');
        }

        $showtime = Showtime::with(['movie', 'room.cinema'])->findOrFail($showtimeId);
        
        // Lấy thông tin các ghế để tính toán tiền ghế
        $selectedSeats = ShowtimeSeat::with('seat.seatType')->whereIn('id', $seatIds)->get();
        $totalSeatPrice = $selectedSeats->sum(function ($ss) use ($showtime) {
            return $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
        });

        // Lấy danh sách Combo bắp nước đang hoạt động
        $combos = Combo::where('status', 'active')->latest()->get();

        // Lấy thông tin combo đã chọn trước đó trong session (nếu có)
        $savedCombos = session()->get("booking.{$showtimeId}.combos", []);

        return view('customer.bookings.select-combos', compact(
            'showtime',
            'selectedSeats',
            'totalSeatPrice',
            'combos',
            'savedCombos'
        ));
    }

    /**
     * Step 2 Processing: Save selected combos to session.
     */
    public function processCombos(Request $request, $showtimeId)
    {
        $combos = $request->input('combos', []);

        // Filter out items with quantity = 0
        $filteredCombos = [];
        foreach ($combos as $comboId => $qty) {
            $qtyVal = intval($qty);
            if ($qtyVal > 0) {
                $filteredCombos[$comboId] = $qtyVal;
            }
        }

        session()->put("booking.{$showtimeId}.combos", $filteredCombos);

        return redirect()->route('booking.checkout', $showtimeId);
    }

    /**
     * Step 3: Checkout summary.
     */
    public function checkout($showtimeId)
    {
        $seatIds = session()->get("booking.{$showtimeId}.seat_ids");
        if (empty($seatIds)) {
            return redirect()->route('booking.select-seats', $showtimeId)->with('error', 'Vui lòng chọn ghế ngồi trước.');
        }

        $showtime = Showtime::with(['movie', 'room.cinema'])->findOrFail($showtimeId);

        // Chi tiết ghế đã chọn
        $selectedSeats = ShowtimeSeat::with('seat.seatType')->whereIn('id', $seatIds)->get();
        $totalSeatPrice = $selectedSeats->sum(function ($ss) use ($showtime) {
            return $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
        });

        // Chi tiết combo đã chọn
        $combosData = session()->get("booking.{$showtimeId}.combos", []);
        $selectedCombos = [];
        $totalComboPrice = 0;

        if (!empty($combosData)) {
            $combos = Combo::whereIn('id', array_keys($combosData))->get();
            foreach ($combos as $combo) {
                $qty = $combosData[$combo->id];
                $subtotal = $combo->price * $qty;
                $totalComboPrice += $subtotal;
                $selectedCombos[] = [
                    'combo' => $combo,
                    'quantity' => $qty,
                    'subtotal' => $subtotal
                ];
            }
        }

        $grandTotal = $totalSeatPrice + $totalComboPrice;

        // Lấy voucher đã áp dụng từ session (nếu có)
        $appliedVoucher  = session()->get("booking.{$showtimeId}.voucher");
        $discountAmount  = 0;
        if ($appliedVoucher) {
            // Tính lại discount theo subtotal hiện tại để tránh giả mạo
            if ($appliedVoucher['discount_type'] === 'percent') {
                $discountAmount = (int) ($grandTotal * ($appliedVoucher['discount_value'] / 100));
            } else {
                $discountAmount = min($appliedVoucher['discount_amount'], $grandTotal);
            }
            // Cập nhật lại discount_amount trong session theo giá trị thực
            $appliedVoucher['discount_amount'] = $discountAmount;
            session()->put("booking.{$showtimeId}.voucher", $appliedVoucher);
        }
        $finalTotal = max(0, $grandTotal - $discountAmount);

        // Lấy tất cả combo đang hoạt động để làm sản phẩm upsell
        $allCombos = Combo::where('status', 'active')->latest()->get();

        return view('customer.bookings.checkout', compact(
            'showtime',
            'selectedSeats',
            'totalSeatPrice',
            'selectedCombos',
            'totalComboPrice',
            'grandTotal',
            'discountAmount',
            'finalTotal',
            'appliedVoucher',
            'allCombos'
        ));
    }

    /**
     * Final action: Save booking to DB.
     */
    public function confirm(Request $request, $showtimeId)
    {
        $seatIds = session()->get("booking.{$showtimeId}.seat_ids");
        if (empty($seatIds)) {
            return redirect()->route('booking.select-seats', $showtimeId)->with('error', 'Vui lòng chọn ghế ngồi trước.');
        }

        // Nhận dữ liệu combo từ request gửi lên hoặc fallback về session
        $combosInput = $request->input('combos', session()->get("booking.{$showtimeId}.combos", []));
        $combosData = [];
        foreach ($combosInput as $comboId => $qty) {
            $qtyVal = intval($qty);
            if ($qtyVal > 0) {
                $combosData[$comboId] = $qtyVal;
            }
        }

        $userId = Auth::id();
        $voucherData = session()->get("booking.{$showtimeId}.voucher");

        try {
            $booking = $this->bookingService->createBooking($userId, $showtimeId, $seatIds, $combosData, $voucherData);

            session()->forget("booking.{$showtimeId}");

            return redirect()->route('booking.success', $booking->id)->with('success', 'Chúc mững bạn đã đặt vé thành công!');
        } catch (Exception $e) {
            return redirect()->route('booking.select-seats', $showtimeId)->with('error', 'Đặt vé thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Show booking success page.
     */
    public function success($bookingId)
    {
        $booking = Booking::with([
            'showtime.movie',
            'showtime.room.cinema',
            'bookingDetails.showtimeSeat.seat.seatType',
            'combos'
        ])->findOrFail($bookingId);

        // Đảm bảo người dùng chỉ xem được đơn hàng của chính mình
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return view('customer.bookings.success', compact('booking'));
    }
}
