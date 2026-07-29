<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\Payment;
use App\Services\ComboOrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComboShopController extends Controller
{
    public function __construct(
        protected ComboOrderService $comboOrderService,
        protected PaymentService $paymentService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Trang danh sách sản phẩm
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /shop/combos
     * Hiển thị trang mua combo/F&B riêng lẻ.
     */
    public function index()
    {
        $combos = Combo::with('items')
            ->where('status', 'active')
            ->latest()
            ->get();

        $comboItems = ComboItem::where('status', 'active')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        // Lấy giỏ hàng từ session
        $cart = session()->get('combo_shop.cart', ['combos' => [], 'items' => []]);

        return view('customer.combo-shop.index', compact('combos', 'comboItems', 'cart'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Quản lý giỏ hàng qua session
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /shop/combos/cart
     * Cập nhật giỏ hàng combo shop (thêm/sửa/xóa).
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'combos'       => 'nullable|array',
            'combos.*'     => 'integer|min:0',
            'combo_items'  => 'nullable|array',
            'combo_items.*'=> 'integer|min:0',
        ]);

        $cart = [
            'combos' => array_filter($request->input('combos', []), fn($qty) => $qty > 0),
            'items'  => array_filter($request->input('combo_items', []), fn($qty) => $qty > 0),
        ];

        session()->put('combo_shop.cart', $cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Trang checkout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /shop/combos/checkout
     * Hiển thị trang xác nhận đơn hàng và chọn phương thức thanh toán.
     */
    public function checkout()
    {
        $cart = session()->get('combo_shop.cart', ['combos' => [], 'items' => []]);

        if (empty($cart['combos']) && empty($cart['items'])) {
            return redirect()->route('combo-shop.index')
                ->with('error', 'Giỏ hàng trống. Vui lòng chọn ít nhất một sản phẩm.');
        }

        // Lấy thông tin combo gói đã chọn
        $selectedCombos = collect();
        if (!empty($cart['combos'])) {
            $combos = Combo::with('items')
                ->whereIn('id', array_keys($cart['combos']))
                ->where('status', 'active')
                ->get();

            $selectedCombos = $combos->map(fn($c) => [
                'combo'    => $c,
                'quantity' => $cart['combos'][$c->id] ?? 0,
                'subtotal' => $c->price * ($cart['combos'][$c->id] ?? 0),
            ]);
        }

        // Lấy thông tin đồ ăn lẻ đã chọn
        $selectedItems = collect();
        if (!empty($cart['items'])) {
            $items = ComboItem::whereIn('id', array_keys($cart['items']))
                ->where('status', 'active')
                ->get();

            $selectedItems = $items->map(fn($i) => [
                'item'     => $i,
                'quantity' => $cart['items'][$i->id] ?? 0,
                'subtotal' => $i->price * ($cart['items'][$i->id] ?? 0),
            ]);
        }

        $totalComboPrice = $selectedCombos->sum('subtotal');
        $totalItemPrice  = $selectedItems->sum('subtotal');
        $subtotal        = $totalComboPrice + $totalItemPrice;

        // Voucher từ session
        $appliedVoucher = session()->get('combo_shop.voucher');
        $discountAmount = 0;
        if ($appliedVoucher) {
            $discountAmount = $appliedVoucher['discount_type'] === 'percent'
                ? (int) ($subtotal * ($appliedVoucher['discount_value'] / 100))
                : min($appliedVoucher['discount_amount'] ?? $appliedVoucher['discount_value'], $subtotal);
        }

        $finalTotal = max(0, $subtotal - $discountAmount);

        return view('customer.combo-shop.checkout', compact(
            'selectedCombos',
            'selectedItems',
            'totalComboPrice',
            'totalItemPrice',
            'subtotal',
            'appliedVoucher',
            'discountAmount',
            'finalTotal'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Xác nhận đơn hàng và chuyển sang cổng thanh toán
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /shop/combos/confirm
     * Tạo đơn hàng và redirect sang VNPay/MoMo.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:vnpay,demo',
        ]);

        $cart = session()->get('combo_shop.cart', ['combos' => [], 'items' => []]);

        if (empty($cart['combos']) && empty($cart['items'])) {
            return redirect()->route('combo-shop.index')
                ->with('error', 'Giỏ hàng trống. Vui lòng chọn sản phẩm.');
        }

        $voucherData = session()->get('combo_shop.voucher');

        try {
            $booking = $this->comboOrderService->createCustomerComboOrder(
                userId:         Auth::id(),
                combosData:     $cart['combos'] ?? [],
                comboItemsData: $cart['items'] ?? [],
                voucherData:    $voucherData
            );

            // Xóa giỏ hàng & voucher khỏi session
            session()->forget(['combo_shop.cart', 'combo_shop.voucher']);

            $paymentMethod = $request->input('payment_method');

            // Demo mode
            $demoMode = filter_var(env('PAYMENT_DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN);
            if ($demoMode || $paymentMethod === 'demo') {
                return redirect()->route('combo-shop.payment.demo', [
                    'id'       => $booking->id,
                    'provider' => $paymentMethod === 'demo' ? 'demo' : $paymentMethod,
                ]);
            }

            // VNPay
            if ($paymentMethod === 'vnpay') {
                try {
                    $bankCode   = $request->input('bank_code', 'NCB');
                    $paymentUrl = $this->paymentService->createVnPayUrl(
                        $booking->booking_code,
                        $booking->final_total,
                        $bankCode
                    );
                } catch (Exception $e) {
                    logger()->warning('ComboShop VNPay init failed', [
                        'booking_code' => $booking->booking_code,
                        'error'        => $e->getMessage(),
                    ]);
                    $paymentUrl = null;
                }

                if ($paymentUrl) {
                    return redirect()->away($paymentUrl);
                }

                return redirect()->route('combo-shop.payment.qr', [
                    'id'          => $booking->id,
                    'provider'    => 'vnpay',
                    'payment_url' => env('VNP_FALLBACK_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
                ]);
            }

            // MoMo
            if ($paymentMethod === 'momo') {
                try {
                    $paymentUrl = $this->paymentService->createMoMoUrl(
                        $booking->booking_code,
                        (int) $booking->final_total
                    );
                } catch (Exception $e) {
                    logger()->warning('ComboShop MoMo init failed', [
                        'booking_code' => $booking->booking_code,
                        'error'        => $e->getMessage(),
                    ]);
                    $paymentUrl = env('MOMO_FALLBACK_URL', 'https://momo.vn/');
                }

                return redirect()->route('combo-shop.payment.qr', [
                    'id'          => $booking->id,
                    'provider'    => 'momo',
                    'payment_url' => $paymentUrl,
                ]);
            }

        } catch (Exception $e) {
            logger()->error('ComboShop confirm failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('combo-shop.checkout')
                ->with('error', 'Tạo đơn hàng thất bại: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Trang kết quả & xử lý callback
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /shop/combos/payment/qr/{id}/{provider}
     */
    public function paymentQrPage($id, $provider, Request $request)
    {
        $booking    = Booking::findOrFail($id);
        $paymentUrl = $request->query('payment_url', '');

        return view('customer.combo-shop.payment-qr', compact('booking', 'provider', 'paymentUrl'));
    }

    /**
     * GET /shop/combos/payment/demo/{id}/{provider}
     * Trang mô phỏng thanh toán (PAYMENT_DEMO_MODE=true).
     */
    public function demoPaymentPage($id, $provider)
    {
        $booking = Booking::findOrFail($id);

        return view('customer.combo-shop.payment-demo', compact('booking', 'provider'));
    }

    /**
     * POST /shop/combos/payment/demo/{id}/{provider}/complete
     * Hoàn tất thanh toán demo.
     */
    public function demoPaymentComplete($id, $provider)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Tạo bản ghi payment demo
        if ($booking->payments()->count() === 0) {
            Payment::create([
                'booking_id'       => $booking->id,
                'transaction_code' => 'DEMO-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'amount'           => $booking->final_total,
                'payment_method'   => $provider,
                'payment_status'   => 'success',
                'paid_at'          => now(),
            ]);
        }

        return redirect()->route('combo-shop.success', $booking->id)
            ->with('success', 'Thanh toán giả lập thành công!');
    }

    /**
     * GET /shop/combos/vnpay-callback
     * Xử lý VNPay callback cho đơn combo.
     */
    public function vnpayCallback(Request $request)
    {
        $payload      = $request->all();
        $vnpSecureHash = $request->get('vnp_SecureHash');
        $vnpTxnRef    = $request->get('vnp_TxnRef');
        $responseCode = $request->get('vnp_ResponseCode');

        // Tìm booking theo booking_code
        $booking = Booking::where('booking_code', $vnpTxnRef)->first();

        if (!$booking) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($responseCode === '00') {
            $booking->update([
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ]);

            if ($booking->payments()->count() === 0) {
                Payment::create([
                    'booking_id'       => $booking->id,
                    'transaction_code' => $request->get('vnp_TransactionNo', 'VNPAY-' . time()),
                    'amount'           => $booking->final_total,
                    'payment_method'   => 'vnpay',
                    'payment_status'   => 'success',
                    'paid_at'          => now(),
                ]);
            }

            return redirect()->route('combo-shop.success', $booking->id)
                ->with('success', 'Thanh toán VNPay thành công!');
        }

        return redirect()->route('combo-shop.checkout')
            ->with('error', 'Thanh toán thất bại hoặc bị hủy. Mã lỗi: ' . $responseCode);
    }

    /**
     * GET /shop/combos/momo-callback
     * Xử lý MoMo callback cho đơn combo.
     */
    public function momoCallback(Request $request)
    {
        $orderId     = $request->get('orderId', '');
        $resultCode  = $request->get('resultCode');

        // Lấy booking_code từ orderId (format: CODE-timestamp)
        $bookingCode = preg_replace('/-\d+$/', '', $orderId);
        $booking     = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($resultCode == 0) {
            $booking->update([
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ]);

            if ($booking->payments()->count() === 0) {
                Payment::create([
                    'booking_id'       => $booking->id,
                    'transaction_code' => $request->get('transId', 'MOMO-' . time()),
                    'amount'           => $booking->final_total,
                    'payment_method'   => 'momo',
                    'payment_status'   => 'success',
                    'paid_at'          => now(),
                ]);
            }

            return redirect()->route('combo-shop.success', $booking->id)
                ->with('success', 'Thanh toán MoMo thành công!');
        }

        return redirect()->route('combo-shop.checkout')
            ->with('error', 'Thanh toán MoMo thất bại. Mã lỗi: ' . $resultCode);
    }

    /**
     * GET /shop/combos/success/{id}
     * Trang thành công sau khi thanh toán.
     */
    public function success($id)
    {
        $booking = Booking::with([
            'combos',
            'comboItems.comboItem',
            'payments',
        ])->where('id', $id)
          ->where('user_id', Auth::id())
          ->firstOrFail();

        return view('customer.combo-shop.success', compact('booking'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Voucher
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /shop/combos/voucher/apply
     */
    public function applyVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);
        $code = strtoupper(trim($request->input('code')));

        $promotion = \App\Models\Promotion::where('code', $code)->first();
        $now       = now();

        if (!$promotion
            || $promotion->status !== 'active'
            || ($promotion->start_date && $now->lt($promotion->start_date))
            || ($promotion->end_date && $now->gt($promotion->end_date))
        ) {
            return back()->with('voucher_error', 'Mã không hợp lệ hoặc đã hết hạn.');
        }

        if ($promotion->usage_limit !== null && $promotion->used_count >= $promotion->usage_limit) {
            return back()->with('voucher_error', 'Mã đã hết lượt sử dụng.');
        }

        session()->put('combo_shop.voucher', [
            'promotion_id'   => $promotion->id,
            'code'           => $promotion->code,
            'discount_type'  => $promotion->discount_type,
            'discount_value' => $promotion->discount_value,
        ]);

        return back()->with('voucher_success', 'Áp dụng mã ' . $code . ' thành công!');
    }

    /**
     * POST /shop/combos/voucher/remove
     */
    public function removeVoucher()
    {
        session()->forget('combo_shop.voucher');

        return back()->with('voucher_success', 'Đã xóa mã giảm giá.');
    }
}
