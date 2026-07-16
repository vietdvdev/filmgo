<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Combo;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService; // Thêm Service xử lý cổng thanh toán
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingController extends Controller
{
    protected $bookingService;
    protected $paymentService; // Khai báo đối tác thanh toán

    public function __construct(BookingService $bookingService, PaymentService $paymentService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
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
            'seat_ids' => 'required|array|min:1|max:8',
            'seat_ids.*' => 'integer|exists:showtime_seats,id',
        ], [
            'seat_ids.required' => 'Vui lòng chọn ít nhất một vị trí ghế ngồi.',
            'seat_ids.max' => 'Bạn chỉ được chọn tối đa 8 ghế ngồi trên một giao dịch.',
        ]);

        $expiresAt = now()->addMinutes(10);

        // Dùng transaction + lockForUpdate để tránh race condition
        $result = DB::transaction(function () use ($showtimeId, $request, $expiresAt) {
            $seats = ShowtimeSeat::with(['seat.seatType'])
                ->where('showtime_id', $showtimeId)
                ->whereIn('id', $request->seat_ids)
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($request->seat_ids)) {
                return 'taken';
            }

            foreach ($seats as $seat) {
                // Chấp nhận ghế mà chính user này đang giữ (holding)
                if ($seat->status === 'holding' && $seat->user_id === Auth::id()) {
                    continue;
                }
                if ($seat->status !== 'available') {
                    return 'taken';
                }
            }

            // 1. Kiểm tra quy tắc ghế đôi Sweetbox
            $sweetboxSeats = $seats->filter(function ($ss) {
                return $ss->seat->seatType->name === 'Sweetbox';
            });

            $selectedRows = $seats->pluck('seat.seat_row')->unique();

            // Lấy tất cả ghế trong các hàng đã chọn để kiểm tra tính kề cạnh
            $allSeatsInRows = ShowtimeSeat::with(['seat.seatType'])
                ->where('showtime_id', $showtimeId)
                ->whereHas('seat', function ($query) use ($selectedRows) {
                    $query->whereIn('seat_row', $selectedRows);
                })
                ->get();

            $seatsByRowAndNumber = [];
            foreach ($allSeatsInRows as $ss) {
                $seatsByRowAndNumber[$ss->seat->seat_row][$ss->seat->seat_number] = $ss;
            }

            $selectedSeatIds = $seats->pluck('id')->toArray();

            if ($sweetboxSeats->isNotEmpty()) {
                foreach ($sweetboxSeats as $ss) {
                    $row = $ss->seat->seat_row;
                    $number = $ss->seat->seat_number;
                    $siblingNumber = ($number % 2 === 1) ? $number + 1 : $number - 1;

                    $siblingSeat = $seatsByRowAndNumber[$row][$siblingNumber] ?? null;
                    if (!$siblingSeat) {
                        return ['error' => "Ghế đôi Sweetbox {$row}{$number} không có ghế cùng cặp hợp lệ."];
                    }

                    if (!in_array($siblingSeat->id, $selectedSeatIds)) {
                        return ['error' => "Ghế đôi Sweetbox {$row}{$number} và {$row}{$siblingNumber} phải được chọn cùng nhau."];
                    }
                }
            }

            // 2. Kiểm tra quy tắc chống ghế trống đơn lẻ (Anti-Orphan)
            foreach ($selectedRows as $row) {
                $rowSeats = $seatsByRowAndNumber[$row] ?? [];

                // Đánh giá trạng thái từng ghế trong hàng sau khi khách chọn
                foreach ($rowSeats as $num => $ss) {
                    $ss->is_selected = in_array($ss->id, $selectedSeatIds);
                    $ss->is_taken = ($ss->status !== 'available' && !($ss->status === 'holding' && $ss->user_id === Auth::id()));
                    $ss->is_available = !$ss->is_selected && !$ss->is_taken;
                }

                // Kiểm tra xem có ghế trống khả dụng nào bị cô lập không
                foreach ($rowSeats as $num => $ss) {
                    if (!$ss->is_available) {
                        continue;
                    }

                    $L = $rowSeats[$num - 1] ?? null;
                    $R = $rowSeats[$num + 1] ?? null;

                    $left_blocked_now = ($L === null || $L->is_taken || $L->is_selected);
                    $right_blocked_now = ($R === null || $R->is_taken || $R->is_selected);

                    if ($left_blocked_now && $right_blocked_now) {
                        // Hiện tại đang bị cô lập thành ghế trống đơn lẻ
                        // Kiểm tra xem trước khi chọn nó đã bị cô lập chưa
                        $left_blocked_before = ($L === null || $L->is_taken);
                        $right_blocked_before = ($R === null || $R->is_taken);

                        $was_orphan_before = ($left_blocked_before && $right_blocked_before);

                        if (!$was_orphan_before) {
                            return ['error' => "Không thể chọn các ghế này vì sẽ để lại một ghế trống đơn lẻ tại vị trí {$row}{$num}."];
                        }
                    }
                }
            }

            // Khóa ghế sang trạng thái holding ngay lập tức
            ShowtimeSeat::where('showtime_id', $showtimeId)
                ->whereIn('id', $request->seat_ids)
                ->update([
                    'status'     => 'holding',
                    'user_id'    => Auth::id(),
                    'locked_at'  => now(),
                    'expires_at' => $expiresAt,
                ]);

            return 'ok';
        });

        if (is_array($result) && isset($result['error'])) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        if ($result === 'taken') {
            return redirect()->back()->withInput()->with('error', 'Một số ghế bạn chọn đã có người đặt trước đó. Vui lòng chọn ghế khác.');
        }

        session()->put("booking.{$showtimeId}.seat_ids", $request->seat_ids);
        session()->put("booking.{$showtimeId}.expires_at", $expiresAt->timestamp);

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

        // Lấy thông tin các ghế để tính toán tiền ghế (Eager load seat.seatType để dự phòng fallback)
        $selectedSeats = ShowtimeSeat::with(['seat.seatType'])->whereIn('id', $seatIds)->get();
        
        // Dự phòng Fallback nếu cột price trong DB là null/0 cho các lịch chiếu cũ
        foreach ($selectedSeats as $ss) {
            if (empty($ss->price) || $ss->price <= 0) {
                $ss->price = $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
            }
        }
        
        // Tính tổng tiền ghế
        $totalSeatPrice = $selectedSeats->sum('price');

        // Lấy danh sách Combo bắp nước đang hoạt động
        $combos = Combo::where('status', 'active')->latest()->get();

        // Lấy thông tin combo đã chọn trước đó trong session (nếu có)
        $savedCombosData = session()->get("booking.{$showtimeId}.combos", []);
        $savedCombos = [];
        foreach ($savedCombosData as $comboId => $c) {
            $savedCombos[$comboId] = $c['quantity'] ?? 0;
        }

        // Lấy thời gian hết hạn giữ ghế
        $holdExpiresAt = session()->get("booking.{$showtimeId}.expires_at", time() + 600);

        return view('customer.bookings.select-combos', compact(
            'showtime',
            'selectedSeats',
            'totalSeatPrice',
            'combos',
            'savedCombos',
            'holdExpiresAt'
        ));
    }

    /**
     * Step 2 Processing: Save selected combos to session (with Snapshot).
     */
    public function processCombos(Request $request, $showtimeId)
    {
        $comboInputs = $request->input('combos', []);

        // Lấy trước danh sách combo từ DB để đối chiếu giá và tên
        $comboIds = array_keys(array_filter($comboInputs, fn($qty) => intval($qty) > 0));
        $combosFromDB = Combo::whereIn('id', $comboIds)->where('status', 'active')->get()->keyBy('id');

        $snapshotCombos = [];

        foreach ($comboInputs as $comboId => $qty) {
            $qtyVal = intval($qty);

            // Nếu số lượng > 0 và Combo đó có tồn tại
            if ($qtyVal > 0 && isset($combosFromDB[$comboId])) {
                $combo = $combosFromDB[$comboId];

                // LƯU DẠNG SNAPSHOT CHỐNG ĐỔI GIÁ
                $snapshotCombos[$comboId] = [
                    'id' => $combo->id,
                    'name' => $combo->combo_name,
                    'price' => $combo->price,          // Giá lúc khách chọn
                    'quantity' => $qtyVal,
                    'subtotal' => $combo->price * $qtyVal
                ];
            }
        }

        session()->put("booking.{$showtimeId}.combos", $snapshotCombos);

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

        // Chi tiết ghế đã chọn (Eager load seat.seatType để dự phòng fallback)
        $selectedSeats = ShowtimeSeat::with(['seat.seatType'])->whereIn('id', $seatIds)->get();
        
        // Dự phòng Fallback nếu cột price trong DB là null/0 cho các lịch chiếu cũ
        foreach ($selectedSeats as $ss) {
            if (empty($ss->price) || $ss->price <= 0) {
                $ss->price = $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
            }
        }
        
        $totalSeatPrice = $selectedSeats->sum('price');

        // Chi tiết combo đã chọn (Lấy từ Session Snapshot không cần Query DB)
        $combosData = session()->get("booking.{$showtimeId}.combos", []);
        $selectedCombos = [];
        $totalComboPrice = 0;

        foreach ($combosData as $c) {
            $totalComboPrice += $c['subtotal'];
            $selectedCombos[] = [
                'name' => $c['name'],
                'quantity' => $c['quantity'],
                'subtotal' => $c['subtotal']
            ];
        }

        $grandTotal = $totalSeatPrice + $totalComboPrice;

        // Lấy voucher đã áp dụng từ session (nếu có)
        $appliedVoucher  = session()->get("booking.{$showtimeId}.voucher");
        $discountAmount  = 0;
        if ($appliedVoucher) {
            if ($appliedVoucher['discount_type'] === 'percent') {
                $discountAmount = (int) ($grandTotal * ($appliedVoucher['discount_value'] / 100));
            } else {
                $discountAmount = min($appliedVoucher['discount_amount'], $grandTotal);
            }
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
     * Final action: Save booking to DB (As pending) & Redirect to Payment Gateway.
     */
    public function confirm(Request $request, $showtimeId)
    {
        // 1. Kiểm tra phương thức thanh toán hợp lệ từ client gửi lên
        $request->validate([
            'payment_method' => 'required|in:vnpay,momo',
        ]);

        $seatIds = session()->get("booking.{$showtimeId}.seat_ids");
        if (empty($seatIds)) {
            return redirect()->route('booking.select-seats', $showtimeId)->with('error', 'Vui lòng chọn ghế ngồi trước.');
        }

        $combosSession = session()->get("booking.{$showtimeId}.combos", []);
        $combosData = [];
        foreach ($combosSession as $comboId => $c) {
            $qty = intval($c['quantity']);
            if ($qty > 0) {
                $combosData[$comboId] = $qty;
            }
        }

        $userId = Auth::id();
        $voucherData = session()->get("booking.{$showtimeId}.voucher");

        try {
            DB::beginTransaction();

            // Khởi tạo booking bằng BookingService của bạn nhưng với trạng thái ban đầu là 'pending'
            $booking = $this->bookingService->createBooking($userId, $showtimeId, $seatIds, $combosData, $voucherData);

            // Cập nhật bổ sung hình thức thanh toán cho đơn hàng
            $booking->update([
                'payment_method' => $request->payment_method,
                'status' => 'pending'
            ]);

            DB::commit();

            $demoMode = filter_var(env('PAYMENT_DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN);
            if ($demoMode) {
                return redirect()->route('booking.payment.demo', [
                    'booking_id' => $booking->id,
                    'provider' => $request->payment_method,
                ]);
            }

            // Gọi PaymentService tạo payload bảo mật và lấy link chuyển hướng đối tác
            if ($request->payment_method === 'vnpay') {
                try {
                    $paymentUrl = $this->paymentService->createVnPayUrl($booking->booking_code, $booking->total_amount);
                } catch (Exception $e) {
                    logger()->warning('VNPay payment initialization failed', [
                        'booking_code' => $booking->booking_code,
                        'error' => $e->getMessage(),
                    ]);
                    $paymentUrl = null;
                }

                return redirect()->route('booking.payment.qr', [
                    'booking_id' => $booking->id,
                    'provider' => 'vnpay',
                    'payment_url' => $paymentUrl ?? env('VNP_FALLBACK_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
                ]);
            } elseif ($request->payment_method === 'momo') {
                try {
                    $paymentUrl = $this->paymentService->createMoMoUrl($booking->booking_code, (int)$booking->total_amount);
                } catch (Exception $e) {
                    logger()->warning('MoMo payment initialization failed, using fallback URL', [
                        'booking_code' => $booking->booking_code,
                        'error' => $e->getMessage(),
                    ]);

                    $paymentUrl = env('MOMO_FALLBACK_URL', 'https://momo.vn/');
                }

                return redirect()->route('booking.payment.qr', [
                    'booking_id' => $booking->id,
                    'provider' => 'momo',
                    'payment_url' => $paymentUrl,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            logger()->error('Payment initialization failed', [
                'showtime_id' => $showtimeId,
                'payment_method' => $request->payment_method,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('booking.checkout', $showtimeId)
                ->with('error', 'Khởi tạo thanh toán thất bại. Vui lòng thử lại sau.');
        }
    }

    public function paymentQrPage($bookingId, $provider, Request $request)
    {
        $booking = Booking::with(['showtime.movie', 'showtime.room.cinema'])->findOrFail($bookingId);
        $paymentUrl = $request->query('payment_url', '');

        return view('customer.bookings.payment-qr', compact('booking', 'provider', 'paymentUrl'));
    }

    public function demoPaymentPage($bookingId, $provider)
    {
        $booking = Booking::with(['showtime.movie', 'showtime.room.cinema'])->findOrFail($bookingId);

        return view('customer.bookings.payment-demo', compact('booking', 'provider'));
    }

    public function demoPaymentComplete($bookingId, $provider)
    {
        $booking = Booking::with('bookingDetails')->findOrFail($bookingId);
        $booking->update(['status' => 'completed', 'paid_at' => now()]);

        ShowtimeSeat::whereIn('id', $booking->bookingDetails->pluck('showtime_seat_id'))->update(['status' => 'booked']);
        session()->forget("booking.{$booking->showtime_id}");

        return redirect()->route('booking.success', $booking->id)->with('success', 'Thanh toán giả lập thành công.');
    }

    public function success($bookingId)
    {
        $booking = Booking::with([
            'showtime.movie',
            'showtime.room.cinema',
            'bookingDetails.showtimeSeat.seat.seatType',
            'combos',
        ])->findOrFail($bookingId);

        return view('customer.bookings.success', compact('booking'));
    }

    /**
     * VNPay Return Callback URL
     */
    public function vnpayCallback(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_SecureHash = $request->get('vnp_SecureHash');
        $inputData = [];

        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Kiểm tra tính toàn vẹn của chữ ký điện tử gửi về
        if ($secureHash === $vnp_SecureHash) {
            $bookingCode = $request->get('vnp_TxnRef');
            $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

            if ($request->get('vnp_ResponseCode') == '00') {
                // Thanh toán thành công: Cập nhật DB và xóa session tiến trình đặt vé
                $booking->update(['status' => 'completed', 'paid_at' => now()]);

                // Giải phóng ghế sang trạng thái đã đặt
                ShowtimeSeat::whereIn('id', $booking->bookingDetails->pluck('showtime_seat_id'))->update(['status' => 'booked']);

                session()->forget("booking.{$booking->showtime_id}");

                return redirect()->route('booking.success', $booking->id)->with('success', 'Thanh toán qua VNPay thành công!');
            } else {
                // Thanh toán thất bại hoặc người dùng hủy đơn
                $booking->update(['status' => 'failed']);
                ShowtimeSeat::whereIn('id', $booking->bookingDetails->pluck('showtime_seat_id'))->update(['status' => 'available']);

                return redirect()->route('booking.checkout', $booking->showtime_id)->with('error', 'Giao dịch không thành công hoặc đã bị hủy.');
            }
        }

        return redirect()->route('home')->with('error', 'Chữ ký điện tử không hợp lệ.');
    }

    /**
     * MoMo Redirect Callback URL
     */
    public function momoCallback(Request $request)
    {
        $secretKey = env('MOMO_SECRET_KEY');

        // Nhận các tham số phản hồi từ MoMo để xác thực chữ ký số điện tử
        $partnerCode = $request->get('partnerCode');
        $orderId     = $request->get('orderId'); // Định dạng ở Service cũ: {booking_code}_{timestamp}
        $requestId   = $request->get('requestId');
        $amount      = $request->get('amount');
        $orderInfo   = $request->get('orderInfo');
        $orderType   = $request->get('orderType');
        $transId     = $request->get('transId');

        $resultCode  = $request->get('resultCode');
        $message     = $request->get('message'); // Dòng 335 cũ đã được đẩy lên thế chỗ dòng lỗi
        $localSign   = $request->get('signature');
        $localSign   = $request->get('signature');

        // 1. Tạo chuỗi ký tự để kiểm tra chữ ký (Raw hash theo đúng thứ tự tài liệu MoMo cung cấp)
        $rawHash = "amount=" . $amount .
            "&message=" . $message .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&orderType=" . $orderType .
            "&partnerCode=" . $partnerCode .
            "&requestId=" . $requestId .
            "&resultCode=" . $resultCode .
            "&transId=" . $transId;

        $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

        // 2. Xác thực tính toàn vẹn dữ liệu
        if ($partnerSignature === $localSign) {
            // Tách lấy booking_code gốc nếu orderId có dạng {booking_code}_{timestamp}
            $bookingCode = explode('_', $orderId)[0];
            $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

            if ($resultCode == 0) {
                // Giao dịch thành công qua MoMo
                $booking->update(['status' => 'completed', 'paid_at' => now()]);

                // Cập nhật trạng thái ghế sang đã đặt
                ShowtimeSeat::whereIn('id', $booking->bookingDetails->pluck('showtime_seat_id'))->update(['status' => 'booked']);

                // Xóa session tiến trình đặt vé
                session()->forget("booking.{$booking->showtime_id}");

                return redirect()->route('booking.success', $booking->id)->with('success', 'Thanh toán qua MoMo thành công!');
            } else {
                // Giao dịch thất bại hoặc bị hủy bỏ
                $booking->update(['status' => 'failed']);
                ShowtimeSeat::whereIn('id', $booking->bookingDetails->pluck('showtime_seat_id'))->update(['status' => 'available']);

                return redirect()->route('booking.checkout', $booking->showtime_id)->with('error', 'Thanh toán thất bại hoặc đã bị hủy: ' . $message);
            }
        }

        return redirect()->route('home')->with('error', 'Chữ ký điện tử MoMo không hợp lệ.');
    }
}
