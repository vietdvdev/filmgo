<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 4 — Tối ưu bảng bookings
 *
 * Thay đổi:
 * 1. THÊM cột subtotal — tổng tiền gốc trước khi giảm giá (vé + combo)
 * 2. THÊM cột promotion_id (FK nullable) — snapshot mã KM đã dùng
 * 3. THÊM cột final_total — số tiền thực tế khách phải trả (chạy song song total_amount)
 *
 * Mục đích tách 3 cột tiền (nguyên tắc kế toán):
 *   subtotal        = Tổng giá gốc (vé + combo)
 *   discount_amount = Số tiền giảm nhờ mã KM (đã có sẵn)
 *   final_total     = subtotal - discount_amount (số tiền thực thu)
 *
 * Lưu ý: Giữ nguyên total_amount (legacy) để không làm vỡ code cũ.
 *        final_total chạy song song trong giai đoạn chuyển đổi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tổng tiền gốc (snapshot: vé + combo, chưa trừ KM)
            $table->unsignedBigInteger('subtotal')
                ->default(0)
                ->after('booking_code')
                ->comment('Tổng tiền ban đầu trước khi áp mã giảm giá (vé + combo). Snapshot bất biến.');

            // FK đến promotion đã dùng (nullable — không phải đơn nào cũng dùng KM)
            $table->foreignId('promotion_id')
                ->nullable()
                ->after('subtotal')
                ->comment('Mã khuyến mãi đã áp dụng cho đơn này (nullable). Snapshot ID.')
                ->constrained('promotions')
                ->nullOnDelete();

            // Tiền thực tế khách phải trả (snapshot = subtotal - discount_amount)
            $table->unsignedBigInteger('final_total')
                ->default(0)
                ->after('discount_amount')
                ->comment('Số tiền thực tế khách thanh toán = subtotal - discount_amount. Chạy song song với total_amount trong giai đoạn chuyển đổi.');
        });

        // ── Backfill dữ liệu cũ: đồng bộ giá trị từ total_amount sang final_total và subtotal ──
        // Với dữ liệu cũ: subtotal = total_amount + discount_amount; final_total = total_amount
        DB::statement('
            UPDATE bookings
            SET
                subtotal    = total_amount + discount_amount,
                final_total = total_amount
            WHERE final_total = 0
        ');
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['subtotal', 'promotion_id', 'final_total']);
        });
    }
};
