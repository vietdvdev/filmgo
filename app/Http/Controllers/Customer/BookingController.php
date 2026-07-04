<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\Booking;
use App\Models\IpnLog;
use App\Models\Payment;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Ticket;
use App\Services\BookingService;
use App\Services\PaymentService; // Thêm Service xử lý cổng thanh toán
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        session()->put("booking.{$showtimeId}.expires_at", time() + 600); // 10 phút giữ ghế

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
                'payment_status' => 'pending',
                'booking_status' => 'pending',
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
        $booking->update([
            'payment_status' => 'paid',
            'booking_status' => 'booked',
        ]);

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
     * VNPay Return Callback URL / IPN handler.
     * Ghi log toàn bộ payload, kiểm tra chữ ký, cập nhật đơn hàng và trả JSON cho cổng thanh toán.
     */
    public function vnpayCallback(Request $request)
    {
        $payload = $request->all();
        $ipnLog = $this->createIpnLog('vnpay', 'callback', $payload, $request->get('vnp_SecureHash'));

        try {
            $isSignatureValid = $this->verifyVnPaySignature($request);

            if (! $isSignatureValid) {
                $ipnLog->update([
                    'signature_status' => 'invalid',
                    'processing_status' => 'failed',
                    'response_code' => $request->get('vnp_ResponseCode'),
                    'message' => 'Chữ ký VNPay không hợp lệ.',
                ]);

                return $this->respondGatewayResult($request, [
                    'RspCode' => '97',
                    'Message' => 'Invalid signature',
                ]);
            }

            $bookingCode = $request->get('vnp_TxnRef');
            $booking = Booking::where('booking_code', $bookingCode)->first();

            if (! $booking) {
                $ipnLog->update([
                    'signature_status' => 'valid',
                    'processing_status' => 'failed',
                    'booking_code' => $bookingCode,
                    'response_code' => $request->get('vnp_ResponseCode'),
                    'message' => 'Không tìm thấy đơn hàng tương ứng.',
                ]);

                return $this->respondGatewayResult($request, [
                    'RspCode' => '01',
                    'Message' => 'Order not found',
                ]);
            }

            $ipnLog->update([
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'transaction_code' => $request->get('vnp_TransactionNo'),
                'gateway_reference' => $request->get('vnp_TraceNo'),
            ]);

            $responseCode = (string) $request->get('vnp_ResponseCode');
            $isSuccess = $responseCode === '00';

            if ($isSuccess) {
                $this->finalizeSuccessfulPayment($booking, $ipnLog, 'vnpay', $request->get('vnp_TransactionNo'), $responseCode, $request->get('vnp_SecureHash'));

                return $this->respondGatewayResult($request, [
                    'RspCode' => '00',
                    'Message' => 'Confirm Success',
                ]);
            }

            $this->finalizeFailedPayment($booking, $ipnLog, 'vnpay', $request->get('vnp_TransactionNo'), $responseCode, 'Giao dịch thất bại hoặc đã bị hủy.');

            return $this->respondGatewayResult($request, [
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);
        } catch (Exception $e) {
            $ipnLog->update([
                'signature_status' => 'valid',
                'processing_status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return $this->respondGatewayResult($request, [
                'RspCode' => '99',
                'Message' => 'Unknown error',
            ], 500);
        }
    }

    /**
     * MoMo Redirect Callback URL / IPN handler.
     */
    public function momoCallback(Request $request)
    {
        $payload = $request->all();
        $ipnLog = $this->createIpnLog('momo', 'callback', $payload, $request->get('signature'));

        try {
            $secretKey = env('MOMO_SECRET_KEY');

            $partnerCode = $request->get('partnerCode');
            $orderId = $request->get('orderId');
            $requestId = $request->get('requestId');
            $amount = $request->get('amount');
            $orderInfo = $request->get('orderInfo');
            $orderType = $request->get('orderType');
            $transId = $request->get('transId');
            $resultCode = $request->get('resultCode');
            $message = $request->get('message');
            $localSign = $request->get('signature');

            $rawHash = 'amount=' . $amount
                . '&message=' . $message
                . '&orderId=' . $orderId
                . '&orderInfo=' . $orderInfo
                . '&orderType=' . $orderType
                . '&partnerCode=' . $partnerCode
                . '&requestId=' . $requestId
                . '&resultCode=' . $resultCode
                . '&transId=' . $transId;

            $partnerSignature = hash_hmac('sha256', $rawHash, $secretKey);
            $isSignatureValid = hash_equals($partnerSignature, $localSign ?? '');

            if (! $isSignatureValid) {
                $ipnLog->update([
                    'signature_status' => 'invalid',
                    'processing_status' => 'failed',
                    'response_code' => (string) $resultCode,
                    'message' => 'Chữ ký MoMo không hợp lệ.',
                ]);

                return $this->respondGatewayResult($request, [
                    'resultCode' => 97,
                    'message' => 'Invalid signature',
                ]);
            }

            $bookingCode = explode('_', $orderId)[0];
            $booking = Booking::where('booking_code', $bookingCode)->first();

            if (! $booking) {
                $ipnLog->update([
                    'signature_status' => 'valid',
                    'processing_status' => 'failed',
                    'booking_code' => $bookingCode,
                    'response_code' => (string) $resultCode,
                    'message' => 'Không tìm thấy đơn hàng tương ứng.',
                ]);

                return $this->respondGatewayResult($request, [
                    'resultCode' => 01,
                    'message' => 'Order not found',
                ]);
            }

            $ipnLog->update([
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'transaction_code' => (string) $transId,
                'gateway_reference' => (string) $requestId,
            ]);

            if ((int) $resultCode === 0) {
                $this->finalizeSuccessfulPayment($booking, $ipnLog, 'momo', (string) $transId, (string) $resultCode, $localSign);

                return $this->respondGatewayResult($request, [
                    'resultCode' => 0,
                    'message' => 'Confirm Success',
                ]);
            }

            $this->finalizeFailedPayment($booking, $ipnLog, 'momo', (string) $transId, (string) $resultCode, $message ?? 'Thanh toán thất bại hoặc đã bị hủy.');

            return $this->respondGatewayResult($request, [
                'resultCode' => (int) $resultCode,
                'message' => 'Confirm Success',
            ]);
        } catch (Exception $e) {
            $ipnLog->update([
                'signature_status' => 'valid',
                'processing_status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return $this->respondGatewayResult($request, [
                'resultCode' => 99,
                'message' => 'Unknown error',
            ], 500);
        }
    }

    /**
     * Tạo bản ghi log callback/IPN trước khi xử lý để đảm bảo mọi payload đều được ghi nhận.
     */
    private function createIpnLog(string $provider, string $eventType, array $payload, ?string $signature): IpnLog
    {
        return IpnLog::create([
            'provider' => $provider,
            'event_type' => $eventType,
            'payload' => $payload,
            'signature' => $signature,
            'signature_status' => 'unknown',
            'processing_status' => 'pending',
            'message' => 'Đang chờ xử lý callback/IPN.',
        ]);
    }

    /**
     * Xác thực chữ ký VNPay bằng HMAC SHA-512 theo đúng chuẩn của cổng thanh toán.
     */
    private function verifyVnPaySignature(Request $request): bool
    {
        $vnpHashSecret = env('VNP_HASH_SECRET');
        $vnpSecureHash = $request->get('vnp_SecureHash');
        $inputData = [];

        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) === 'vnp_') {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);

        $hashData = '';
        $isFirst = true;
        foreach ($inputData as $key => $value) {
            if ($isFirst) {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $isFirst = false;
            } else {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        return hash_equals($secureHash, $vnpSecureHash ?? '');
    }

    /**
     * Xử lý thanh toán thành công trong một transaction duy nhất để đảm bảo dữ liệu không bị lệch.
     */
    private function finalizeSuccessfulPayment(Booking $booking, IpnLog $ipnLog, string $provider, ?string $transactionCode, ?string $responseCode, ?string $signature): void
    {
        DB::transaction(function () use ($booking, $ipnLog, $provider, $transactionCode, $responseCode, $signature): void {
            $booking->refresh();

            if ($booking->payment_status === 'paid' && $booking->booking_status === 'booked') {
                return;
            }

            $booking->update([
                'payment_status' => 'paid',
                'booking_status' => 'booked',
            ]);

            $booking->payments()->create([
                'transaction_code' => $transactionCode,
                'amount' => $booking->total_amount,
                'payment_method' => $provider,
                'payment_status' => 'success',
                'paid_at' => now(),
            ]);

            $ticketIds = Ticket::whereIn('booking_detail_id', $booking->bookingDetails()->pluck('id'))->pluck('id');
            Ticket::whereIn('id', $ticketIds)->update(['ticket_status' => 'booked']);

            $showtimeSeatIds = $booking->bookingDetails()->pluck('showtime_seat_id');
            ShowtimeSeat::whereIn('id', $showtimeSeatIds)->update(['status' => 'booked']);

            session()->forget("booking.{$booking->showtime_id}");
        });

        $ipnLog->update([
            'signature_status' => 'valid',
            'processing_status' => 'success',
            'response_code' => $responseCode,
            'message' => 'Thanh toán thành công và đã cập nhật trạng thái vé.',
        ]);
    }

    /**
     * Xử lý thanh toán thất bại hoặc bị hủy, đồng thời rollback trạng thái ghế và vé về trạng thái ban đầu.
     */
    private function finalizeFailedPayment(Booking $booking, IpnLog $ipnLog, string $provider, ?string $transactionCode, ?string $responseCode, string $message): void
    {
        DB::transaction(function () use ($booking, $ipnLog, $provider, $transactionCode, $responseCode, $message): void {
            $booking->update([
                'payment_status' => 'failed',
                'booking_status' => 'cancelled',
            ]);

            $booking->payments()->create([
                'transaction_code' => $transactionCode,
                'amount' => $booking->total_amount,
                'payment_method' => $provider,
                'payment_status' => 'failed',
                'paid_at' => null,
            ]);

            $ticketIds = Ticket::whereIn('booking_detail_id', $booking->bookingDetails()->pluck('id'))->pluck('id');
            Ticket::whereIn('id', $ticketIds)->update(['ticket_status' => 'cancelled']);

            $showtimeSeatIds = $booking->bookingDetails()->pluck('showtime_seat_id');
            ShowtimeSeat::whereIn('id', $showtimeSeatIds)->update(['status' => 'available']);
        });

        $ipnLog->update([
            'signature_status' => 'valid',
            'processing_status' => 'failed',
            'response_code' => $responseCode,
            'message' => $message,
        ]);
    }

    /**
     * Trả về phản hồi theo đúng định dạng mà cổng thanh toán cần để xác nhận đã xử lý request.
     */
    private function respondGatewayResult(Request $request, array $payload, int $statusCode = 200)
    {
        if ($request->isMethod('post') || $request->expectsJson()) {
            return response()->json($payload, $statusCode);
        }

        return redirect()->route('home')->with('success', 'Đã xử lý callback thanh toán.');
    }
}
