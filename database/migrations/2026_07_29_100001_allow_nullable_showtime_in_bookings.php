<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cho phép showtime_id nullable (đơn combo-only không cần suất chiếu).
     * Thêm cột booking_type để phân biệt đơn vé vs đơn combo riêng lẻ.
     */
    public function up(): void
    {
        // 1. Cho phép showtime_id nhận giá trị NULL
        DB::statement('ALTER TABLE bookings MODIFY showtime_id BIGINT UNSIGNED NULL');

        Schema::table('bookings', function (Blueprint $table) {
            // 2. Loại đơn hàng: ticket (đặt vé), combo_only (chỉ mua combo/đồ ăn)
            $table->enum('booking_type', ['ticket', 'combo_only'])
                  ->default('ticket')
                  ->after('channel')
                  ->comment('Loại đơn: ticket = đặt vé phim, combo_only = mua combo/F&B riêng lẻ');
        });
    }

    /**
     * Rollback.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('booking_type');
        });

        // Khôi phục showtime_id về NOT NULL
        DB::statement('ALTER TABLE bookings MODIFY showtime_id BIGINT UNSIGNED NOT NULL');
    }
};
