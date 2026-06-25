<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingCombo;
use App\Models\Combo;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        })->get();

        $showtimes = Showtime::all();
        $combos = Combo::all();
        $promotions = Promotion::all();

        if ($customers->isEmpty() || $showtimes->isEmpty() || $combos->isEmpty()) {
            return;
        }

        // Tạo khoảng 15 giao dịch đặt vé ngẫu nhiên
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $showtime = $showtimes->random();

            // Tìm các ghế trống trong suất chiếu này
            $availableSeats = ShowtimeSeat::with('seat.seatType')
                ->where('showtime_id', $showtime->id)
                ->where('status', 'available')
                ->limit(rand(1, 2))
                ->get();

            if ($availableSeats->isEmpty()) {
                continue;
            }

            // Tính tổng tiền ghế
            $seatAmount = 0;
            $ticketPrices = [];

            foreach ($availableSeats as $showtimeSeat) {
                $basePrice = $showtime->base_price;
                $surcharge = $showtimeSeat->seat->seatType->surcharge_price;
                $finalSeatPrice = $basePrice + $surcharge;

                $seatAmount += $finalSeatPrice;
                $ticketPrices[$showtimeSeat->id] = $finalSeatPrice;
            }

            // Chọn combo đồ ăn (50% có mua kèm combo)
            $comboSelected = null;
            $comboQty = 0;
            $comboSubtotal = 0;
            if (rand(0, 1) === 1) {
                $comboSelected = $combos->random();
                $comboQty = rand(1, 2);
                $comboSubtotal = $comboSelected->price * $comboQty;
            }

            $subtotal = $seatAmount + $comboSubtotal;

            // Áp mã giảm giá (30% có áp mã)
            $promoApplied = null;
            $discountAmount = 0;
            if (rand(1, 10) <= 3) {
                $promoApplied = $promotions->random();
                if ($subtotal >= $promoApplied->min_order_amount) {
                    if ($promoApplied->discount_type === 'percent') {
                        $discountAmount = (int)($subtotal * ($promoApplied->discount_value / 100));
                    } else {
                        $discountAmount = $promoApplied->discount_value;
                    }
                } else {
                    $promoApplied = null;
                }
            }

            $totalAmount = max(0, $subtotal - $discountAmount);

            // 1. Tạo đơn hàng booking
            $booking = Booking::create([
                'user_id' => $customer->id,
                'showtime_id' => $showtime->id,
                'booking_code' => 'FLM-' . Str::upper(Str::random(6)) . rand(100, 999),
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
                'expired_at' => now()->addMinutes(15),
                'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
            ]);

            // 2. Cập nhật trạng thái ghế trong showtime_seats và tạo booking_details
            foreach ($availableSeats as $showtimeSeat) {
                $showtimeSeat->update([
                    'status' => 'booked',
                    'user_id' => $customer->id,
                ]);

                $detail = BookingDetail::create([
                    'booking_id' => $booking->id,
                    'showtime_seat_id' => $showtimeSeat->id,
                    'price' => $ticketPrices[$showtimeSeat->id],
                ]);

                // 3. Sinh vé điện tử cho từng ghế đã đặt
                Ticket::create([
                    'booking_detail_id' => $detail->id,
                    'qr_code' => 'TKT-' . Str::upper(Str::random(12)),
                    'ticket_status' => 'unused',
                    'checked_in_at' => null,
                ]);
            }

            // 4. Tạo booking_combos nếu có chọn mua
            if ($comboSelected) {
                BookingCombo::create([
                    'booking_id' => $booking->id,
                    'combo_id' => $comboSelected->id,
                    'quantity' => $comboQty,
                    'subtotal' => $comboSubtotal,
                ]);
            }

            // 5. Tạo booking_promotions nếu có áp mã
            if ($promoApplied) {
                $booking->promotions()->attach($promoApplied->id);
            }

            // 6. Tạo lịch sử giao dịch thanh toán (Payments)
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_code' => 'PAY-' . Str::upper(Str::random(10)),
                'amount' => $totalAmount,
                'payment_method' => fake()->randomElement(['Momo', 'VNPay', 'ZaloPay', 'Credit']),
                'payment_status' => 'success',
                'paid_at' => $booking->created_at,
            ]);
        }
    }
}
