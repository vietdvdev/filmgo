<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('user_id')->comment('Khách hàng đặt vé')->constrained('users')->cascadeOnDelete();
            $table->foreignId('showtime_id')->comment('Suất chiếu tương ứng')->constrained('showtimes')->cascadeOnDelete();
            $table->string('booking_code', 100)->unique()->comment('Mã tra cứu đơn (VD: FLM-9283)');
            $table->integer('total_amount')->comment('Tổng tiền sau cùng cần thanh toán');
            $table->integer('discount_amount')->default(0)->comment('Số tiền đã được giảm (lưu vết đối soát)');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->comment('Trạng thái thanh toán');
            $table->enum('booking_status', ['pending', 'confirmed', 'cancelled'])->default('pending')->comment('Trạng thái xác nhận vé');
            $table->timestamp('expired_at')->comment('Hạn chót thanh toán - quá hạn hủy tự động');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
