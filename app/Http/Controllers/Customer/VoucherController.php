<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Áp dụng mã voucher cho suất chiếu đang đặt vé.
     * Trả về JSON để xử lý phía frontend không reload trang.
     */
    public function apply(Request $request, $showtimeId)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:50',
            'subtotal'     => 'required|numeric|min:0',
        ]);

        $code    = strtoupper(trim($request->input('voucher_code')));
        $subtotal = (int) $request->input('subtotal');

        // --- Bước 1: Tìm voucher ---
        $promotion = Promotion::where('code', $code)->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá "' . $code . '" không tồn tại.',
            ], 422);
        }

        // --- Bước 2: Kiểm tra trạng thái active ---
        if ($promotion->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này hiện không còn hoạt động.',
            ], 422);
        }

        // --- Bước 3: Kiểm tra thời hạn ---
        $now = now();
        if ($promotion->start_date && $now->lt($promotion->start_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá chưa đến ngày có hiệu lực (từ ' . $promotion->start_date->format('d/m/Y') . ').',
            ], 422);
        }

        if ($promotion->end_date && $now->gt($promotion->end_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết hạn vào ngày ' . $promotion->end_date->format('d/m/Y') . '.',
            ], 422);
        }

        // --- Bước 4: Kiểm tra số lượng tổng còn lại ---
        if ($promotion->quantity !== null) {
            $totalUsed = $promotion->bookings()->count();
            if ($totalUsed >= $promotion->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết số lượt.',
                ], 422);
            }
        }

        // --- Bước 5: Kiểm tra số lượt dùng của user hiện tại ---
        if ($promotion->max_uses_per_user !== null) {
            $usedByUser = $promotion->bookings()
                ->where('user_id', Auth::id())
                ->count();

            if ($usedByUser >= $promotion->max_uses_per_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã sử dụng mã giảm giá này đủ số lần cho phép (' . $promotion->max_uses_per_user . ' lần).',
                ], 422);
            }
        }

        // --- Bước 6: Kiểm tra giá trị đơn hàng tối thiểu ---
        if ($subtotal < $promotion->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng cần tối thiểu ' . number_format($promotion->min_order_amount) . 'đ để áp dụng mã này (hiện tại: ' . number_format($subtotal) . 'đ).',
            ], 422);
        }

        // --- Bước 7: Tính tiền giảm ---
        if ($promotion->discount_type === 'percent') {
            $discountAmount = (int) ($subtotal * ($promotion->discount_value / 100));
        } else {
            $discountAmount = min($promotion->discount_value, $subtotal);
        }

        $finalTotal = max(0, $subtotal - $discountAmount);

        // --- Bước 8: Lưu vào session ---
        session()->put("booking.{$showtimeId}.voucher", [
            'promotion_id'    => $promotion->id,
            'code'            => $promotion->code,
            'discount_type'   => $promotion->discount_type,
            'discount_value'  => $promotion->discount_value,
            'discount_amount' => $discountAmount,
        ]);

        return response()->json([
            'success'         => true,
            'message'         => 'Áp dụng mã giảm giá thành công! Bạn được giảm ' . number_format($discountAmount) . 'đ.',
            'code'            => $promotion->code,
            'discount_type'   => $promotion->discount_type,
            'discount_value'  => $promotion->discount_value,
            'discount_amount' => $discountAmount,
            'final_total'     => $finalTotal,
        ]);
    }

    /**
     * Xóa voucher đang áp dụng khỏi session.
     */
    public function remove(Request $request, $showtimeId)
    {
        session()->forget("booking.{$showtimeId}.voucher");

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa mã giảm giá.',
        ]);
    }
}
