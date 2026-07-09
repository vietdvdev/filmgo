<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm các cột hỗ trợ bán vé tại quầy (POS) vào bảng bookings.
     * - staff_id : Nhân viên tạo đơn (null = khách đặt online)
     * - channel  : Kênh đặt vé (online | counter)
     * Đồng thời cho phép user_id nullable để hỗ trợ khách vãng lai.
     */
    public function up(): void
    {
        // 1. Cho phép user_id nhận giá trị NULL (khách vãng lai không cần tài khoản)
        DB::statement('ALTER TABLE bookings MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('bookings', function (Blueprint $table) {
            // 2. ID nhân viên thực hiện giao dịch (NULL nếu khách tự đặt online)
            $table->unsignedBigInteger('staff_id')
                  ->nullable()
                  ->after('user_id')
                  ->comment('Nhân viên tạo đơn tại quầy (NULL = đặt online)');

            // 3. Kênh bán vé để phân biệt online vs tại quầy
            $table->enum('channel', ['online', 'counter'])
                  ->default('online')
                  ->after('booking_status')
                  ->comment('Kênh bán: online hoặc counter (tại quầy)');

            // 4. Foreign key tới users
            $table->foreign('staff_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Rollback: xóa các cột POS và khôi phục user_id NOT NULL.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropColumn(['staff_id', 'channel']);
        });

        // Khôi phục user_id về NOT NULL
        DB::statement('ALTER TABLE bookings MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};
