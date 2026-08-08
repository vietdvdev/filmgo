<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCombo;
use App\Models\BookingComboItem;
use App\Models\BookingDetail;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class ComboOrderService
{
    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Tạo đơn combo online (Customer) — trạng thái pending, chờ thanh toán
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tạo đơn hàng combo/F&B trực tuyến cho khách hàng.
     * Trạng thái: payment_status = 'pending', chờ redirect sang cổng thanh toán.
     *
     * @param  int         $userId        ID khách hàng đã đăng nhập
     * @param  array       $combosData    ['combo_id' => quantity, ...]
     * @param  array       $comboItemsData ['combo_item_id' => quantity, ...]
     * @param  array|null  $voucherData   ['promotion_id', 'discount_type', 'discount_value'] từ session
     * @return Booking
     *
     * @throws Exception
     */
    public function createCustomerComboOrder(
        int $userId,
        array $combosData = [],
        array $comboItemsData = [],
        ?array $voucherData = null,
        ?int $cinemaId = null
    ): Booking {
        $this->validateNotEmpty($combosData, $comboItemsData);

        return DB::transaction(function () use ($userId, $combosData, $comboItemsData, $voucherData, $cinemaId) {
            // ── 1. Tính giá combo gói ─────────────────────────────────────
            [$totalComboPrice, $combosToInsert] = $this->calcCombos($combosData);

            // ── 2. Tính giá đồ ăn lẻ ─────────────────────────────────────
            [$totalItemPrice, $itemsToInsert] = $this->calcComboItems($comboItemsData);

            // ── 3. Áp voucher ─────────────────────────────────────────────
            [$discountAmount, $promotionId] = $this->applyVoucher(
                $voucherData,
                $totalComboPrice + $totalItemPrice
            );

            $subtotal    = $totalComboPrice + $totalItemPrice;
            $finalTotal  = max(0, $subtotal - $discountAmount);

            // ── 4. Sinh mã booking ────────────────────────────────────────
            $bookingCode = $this->generateBookingCode('SHOP');

            // ── 5. Tạo Booking (pending — chờ thanh toán online) ──────────
            $booking = Booking::create([
                'user_id'         => $userId,
                'staff_id'        => null,
                'showtime_id'     => null,               // Không có suất chiếu
                'cinema_id'       => $cinemaId,
                'booking_code'    => $bookingCode,
                'subtotal'        => $subtotal,
                'promotion_id'    => $promotionId,
                'total_amount'    => $subtotal,
                'discount_amount' => $discountAmount,
                'final_total'     => $finalTotal,
                'payment_status'  => 'pending',
                'booking_status'  => 'pending',
                'channel'         => 'online',
                'booking_type'    => 'combo_only',
                'expired_at'      => now()->addMinutes(30),
            ]);

            // ── 6. Lưu combos và đồ lẻ ───────────────────────────────────
            $this->insertCombos($booking->id, $combosToInsert);
            $this->insertComboItems($booking->id, $itemsToInsert);

            // ── 7. Tạo booking detail + ticket QR cho đơn combo ───────
            $this->createComboReceiptTicket($booking->id);

            // ── 8. Gắn voucher ────────────────────────────────────────────
            if ($promotionId) {
                $booking->promotions()->attach($promotionId);
            }

            return $booking->load(['combos', 'comboItems.comboItem', 'bookingDetails.ticket']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Tạo đơn F&B tại quầy (Staff POS) — hoàn tất ngay
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tạo đơn bán F&B/combo tại quầy (POS), không cần suất chiếu.
     * Hoàn tất ngay: payment_status = 'paid', booking_status = 'confirmed'.
     *
     * @param  int         $staffId
     * @param  array       $combosData      ['combo_id' => quantity, ...]
     * @param  array       $comboItemsData  ['combo_item_id' => quantity, ...]
     * @param  string      $paymentMethod   'cash' | 'transfer'
     * @param  string|null $customerPhone   SĐT khách (tuỳ chọn)
     * @param  string|null $voucherCode     Mã giảm giá (tuỳ chọn)
     * @return Booking
     *
     * @throws Exception
     */
    public function createCounterFnbOrder(
        int $staffId,
        array $combosData = [],
        array $comboItemsData = [],
        string $paymentMethod = 'cash',
        ?string $customerPhone = null,
        ?string $voucherCode = null
    ): Booking {
        $this->validateNotEmpty($combosData, $comboItemsData);

        return DB::transaction(function () use (
            $staffId, $combosData, $comboItemsData,
            $paymentMethod, $customerPhone, $voucherCode
        ) {
            // ── 1. Tính giá combo gói ─────────────────────────────────────
            [$totalComboPrice, $combosToInsert] = $this->calcCombos($combosData);

            // ── 2. Tính giá đồ ăn lẻ ─────────────────────────────────────
            [$totalItemPrice, $itemsToInsert] = $this->calcComboItems($comboItemsData);

            // ── 3. Tìm khách hàng theo SĐT ───────────────────────────────
            $customerId = null;
            if ($customerPhone) {
                $customer   = User::where('phone', $customerPhone)->first();
                $customerId = $customer?->id;
            }

            // ── 4. Áp voucher ─────────────────────────────────────────────
            [$discountAmount, $promotionId] = $this->applyVoucherByCode(
                $voucherCode,
                $totalComboPrice + $totalItemPrice
            );

            $subtotal   = $totalComboPrice + $totalItemPrice;
            $finalTotal = max(0, $subtotal - $discountAmount);

            // ── 5. Sinh mã booking ────────────────────────────────────────
            $bookingCode = $this->generateBookingCode('FNB');

            // ── 6. Tạo Booking — hoàn tất ngay ───────────────────────────
            $booking = Booking::create([
                'user_id'         => $customerId,
                'staff_id'        => $staffId,
                'showtime_id'     => null,               // Không có suất chiếu
                'booking_code'    => $bookingCode,
                'subtotal'        => $subtotal,
                'promotion_id'    => $promotionId,
                'total_amount'    => $subtotal,
                'discount_amount' => $discountAmount,
                'final_total'     => $finalTotal,
                'payment_status'  => 'paid',             // Hoàn tất tại quầy
                'booking_status'  => 'confirmed',
                'channel'         => 'counter',
                'booking_type'    => 'combo_only',
                'expired_at'      => now()->addMinutes(30),
            ]);

            // ── 7. Lưu combos và đồ lẻ ───────────────────────────────────
            $this->insertCombos($booking->id, $combosToInsert);
            $this->insertComboItems($booking->id, $itemsToInsert);

            // ── 8. Tạo booking detail + ticket QR cho đơn combo ───────
            $this->createComboReceiptTicket($booking->id);

            // ── 9. Gắn voucher ────────────────────────────────────────────
            if ($promotionId) {
                $booking->promotions()->attach($promotionId);
                Promotion::where('id', $promotionId)->increment('used_count');
            }

            // ── 10. Tạo bản ghi Payment ───────────────────────────────────
            Payment::create([
                'booking_id'       => $booking->id,
                'transaction_code' => 'FNB-' . strtoupper(Str::random(10)),
                'amount'           => $finalTotal,
                'payment_method'   => $paymentMethod,
                'payment_status'   => 'success',
                'paid_at'          => now(),
            ]);

            return $booking->load(['combos', 'comboItems.comboItem', 'bookingDetails.ticket', 'payments']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Đảm bảo đơn hàng có ít nhất 1 sản phẩm.
     */
    private function validateNotEmpty(array $combosData, array $comboItemsData): void
    {
        $hasCombo  = !empty(array_filter($combosData, fn($qty) => $qty > 0));
        $hasItem   = !empty(array_filter($comboItemsData, fn($qty) => $qty > 0));

        if (!$hasCombo && !$hasItem) {
            throw new Exception('Vui lòng chọn ít nhất một combo hoặc một món đồ ăn.');
        }
    }

    /**
     * Tính giá và chuẩn bị dữ liệu insert cho combo gói.
     *
     * @return array [totalPrice, itemsToInsert[]]
     */
    private function calcCombos(array $combosData): array
    {
        $total          = 0;
        $toInsert       = [];

        if (empty($combosData)) {
            return [$total, $toInsert];
        }

        $combos = Combo::whereIn('id', array_keys($combosData))
            ->where('status', 'active')
            ->get();

        foreach ($combos as $combo) {
            $qty = intval($combosData[$combo->id] ?? 0);
            if ($qty > 0) {
                $subtotal   = $combo->price * $qty;
                $total     += $subtotal;
                $toInsert[] = [
                    'combo_id' => $combo->id,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return [$total, $toInsert];
    }

    /**
     * Tính giá và chuẩn bị dữ liệu insert cho đồ ăn/uống lẻ từng món.
     *
     * @return array [totalPrice, itemsToInsert[]]
     */
    private function calcComboItems(array $comboItemsData): array
    {
        $total    = 0;
        $toInsert = [];

        if (empty($comboItemsData)) {
            return [$total, $toInsert];
        }

        $items = ComboItem::whereIn('id', array_keys($comboItemsData))
            ->where('status', 'active')
            ->get();

        foreach ($items as $item) {
            $qty = intval($comboItemsData[$item->id] ?? 0);
            if ($qty > 0) {
                $subtotal   = $item->price * $qty;
                $total     += $subtotal;
                $toInsert[] = [
                    'combo_item_id' => $item->id,
                    'quantity'      => $qty,
                    'unit_price'    => $item->price,
                    'subtotal'      => $subtotal,
                ];
            }
        }

        return [$total, $toInsert];
    }

    /**
     * Áp voucher từ session data (Customer online).
     *
     * @param  array|null $voucherData  ['promotion_id', 'discount_type', 'discount_value']
     * @param  int        $subtotal
     * @return array      [$discountAmount, $promotionId]
     */
    private function applyVoucher(?array $voucherData, int $subtotal): array
    {
        if (!$voucherData || empty($voucherData['promotion_id'])) {
            return [0, null];
        }

        $promotion = Promotion::where('id', $voucherData['promotion_id'])
            ->where('status', 'active')
            ->first();

        if (!$promotion) {
            return [0, null];
        }

        $discount = $voucherData['discount_type'] === 'percent'
            ? (int) ($subtotal * ($voucherData['discount_value'] / 100))
            : min($voucherData['discount_value'], $subtotal);

        return [$discount, $promotion->id];
    }

    /**
     * Áp voucher từ mã code (Staff POS).
     *
     * @param  string|null $voucherCode
     * @param  int         $subtotal
     * @return array       [$discountAmount, $promotionId]
     */
    private function applyVoucherByCode(?string $voucherCode, int $subtotal): array
    {
        if (!$voucherCode) {
            return [0, null];
        }

        $promotion = Promotion::where('code', strtoupper(trim($voucherCode)))->first();
        $now       = now();

        $isValid = $promotion
            && $promotion->status === 'active'
            && ($promotion->start_date === null || $now->gte($promotion->start_date))
            && ($promotion->end_date   === null || $now->lte($promotion->end_date));

        if ($isValid && $promotion->usage_limit !== null) {
            if ($promotion->used_count >= $promotion->usage_limit) {
                $isValid = false;
            }
        }

        if (!$isValid) {
            return [0, null];
        }

        $discount = $promotion->discount_type === 'percent'
            ? (int) ($subtotal * ($promotion->discount_value / 100))
            : min($promotion->discount_value, $subtotal);

        return [$discount, $promotion->id];
    }

    /**
     * Batch insert booking_combos (combo gói).
     */
    private function insertCombos(int $bookingId, array $combosToInsert): void
    {
        if (empty($combosToInsert)) {
            return;
        }

        $data = array_map(fn($c) => [
            'booking_id' => $bookingId,
            'combo_id'   => $c['combo_id'],
            'quantity'   => $c['quantity'],
            'subtotal'   => $c['subtotal'],
        ], $combosToInsert);

        BookingCombo::insert($data);
    }

    /**
     * Batch insert booking_combo_items (đồ ăn lẻ từng món).
     */
    private function insertComboItems(int $bookingId, array $itemsToInsert): void
    {
        if (empty($itemsToInsert)) {
            return;
        }

        $now  = now()->toDateTimeString();
        $data = array_map(fn($i) => [
            'booking_id'    => $bookingId,
            'combo_item_id' => $i['combo_item_id'],
            'quantity'      => $i['quantity'],
            'unit_price'    => $i['unit_price'],
            'subtotal'      => $i['subtotal'],
            'created_at'    => $now,
            'updated_at'    => $now,
        ], $itemsToInsert);

        BookingComboItem::insert($data);
    }

    private function createComboReceiptTicket(int $bookingId): void
    {
        $detail = BookingDetail::create([
            'booking_id'       => $bookingId,
            'showtime_seat_id' => null,
            'price'            => 0,
        ]);

        Ticket::create([
            'booking_detail_id' => $detail->id,
            'qr_code'           => 'CB-' . strtoupper(Str::random(12)) . '-' . $detail->id,
            'ticket_status'     => 'unused',
        ]);
    }

    /**
     * Sinh booking_code duy nhất với prefix cho trước.
     */
    private function generateBookingCode(string $prefix): string
    {
        do {
            $code = $prefix . '-' . strtoupper(Str::random(8));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
