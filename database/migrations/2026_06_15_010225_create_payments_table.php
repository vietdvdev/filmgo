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
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('booking_id')->comment('Đơn đặt vé được thanh toán')->constrained('bookings')->cascadeOnDelete();
            $table->string('transaction_code', 100)->nullable()->comment('Mã giao dịch từ cổng thanh toán');
            $table->integer('amount')->comment('Số tiền giao dịch (VNĐ)');
            $table->string('payment_method', 50)->comment('Phương thức: Credit, Momo, VNPay, ZaloPay');
            $table->enum('payment_status', ['pending', 'success', 'failed', 'refunded'])->comment('Trạng thái cổng báo về');
            $table->timestamp('paid_at')->nullable()->comment('Thời điểm thanh toán thành công');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
