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
        Schema::create('conflict_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('booking_code', 100)->nullable()->comment('Mã booking liên quan');
            $table->string('transaction_code', 100)->nullable()->comment('Mã giao dịch từ cổng thanh toán');
            $table->integer('amount')->comment('Số tiền giao dịch');
            $table->string('payment_method', 50)->comment('Phương thức: momo, vnpay...');
            $table->text('reason')->nullable()->comment('Lý do lỗi/xung đột');
            $table->enum('status', ['pending', 'resolved'])->default('pending')->comment('Trạng thái xử lý');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflict_payments');
    }
};
