<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 2 — Tối ưu bảng showtime_seats
 *
 * Thay đổi:
 * 1. Thêm cột price (bigint) — snapshot giá cuối cùng của ghế khi suất được khởi tạo
 *    = showtime.base_price + seat_type.surcharge_price
 *    Giúp giỏ hàng chỉ cần SUM(price) thay vì tính lại công thức mỗi lần
 *
 * 2. Mở rộng enum status: thêm giá trị 'maintenance' và chuẩn hóa 'hold' bên cạnh 'holding'
 *    Tập hợp mới: ['available', 'hold', 'holding', 'booked', 'maintenance']
 *    - available:   ghế trống, có thể đặt
 *    - hold/holding: đang giữ chỗ (chưa thanh toán) — hold là alias mới, holding là legacy
 *    - booked:      đã đặt, đã thanh toán
 *    - maintenance: ghế bảo trì/hỏng trong suất này
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bước 1: Thêm cột price ──────────────────────────────────────────────
        Schema::table('showtime_seats', function (Blueprint $table) {
            $table->unsignedBigInteger('price')
                ->default(0)
                ->after('user_id')
                ->comment('Giá cuối cùng của ghế này trong suất chiếu (snapshot = base_price + surcharge). Dùng SUM(price) để tính tổng giỏ hàng.');
        });

        // ── Bước 2: Mở rộng enum status ────────────────────────────────────────
        DB::statement("
            ALTER TABLE showtime_seats
            MODIFY COLUMN status
            ENUM('available', 'hold', 'holding', 'booked', 'maintenance')
            NOT NULL
            DEFAULT 'available'
            COMMENT 'Trạng thái ghế: available=trống, hold/holding=đang giữ, booked=đã bán, maintenance=bảo trì'
        ");
    }

    public function down(): void
    {
        // ── Khôi phục enum về trạng thái cũ ────────────────────────────────────
        DB::statement("
            ALTER TABLE showtime_seats
            MODIFY COLUMN status
            ENUM('available', 'holding', 'booked')
            NOT NULL
            DEFAULT 'available'
        ");

        Schema::table('showtime_seats', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
