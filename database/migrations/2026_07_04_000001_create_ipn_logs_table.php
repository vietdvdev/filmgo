<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration để tạo bảng ghi log callback/IPN.
     */
    public function up(): void
    {
        Schema::create('ipn_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->comment('Tên cổng thanh toán: vnpay, momo, ...');
            $table->string('event_type', 50)->nullable()->comment('Loại sự kiện: callback, ipn, redirect');
            $table->string('booking_code', 100)->nullable()->comment('Mã đơn đặt vé');
            $table->unsignedBigInteger('booking_id')->nullable()->comment('ID đơn đặt vé nếu đã tìm thấy');
            $table->string('transaction_code', 100)->nullable()->comment('Mã giao dịch từ cổng thanh toán');
            $table->string('gateway_reference', 100)->nullable()->comment('Mã tham chiếu từ cổng thanh toán');
            $table->string('signature_status', 20)->default('unknown')->comment('Trạng thái xác thực chữ ký: valid/invalid/unknown');
            $table->string('processing_status', 20)->default('pending')->comment('Trạng thái xử lý: pending/success/failed');
            $table->string('response_code', 20)->nullable()->comment('Mã phản hồi từ cổng thanh toán');
            $table->json('payload')->comment('Toàn bộ payload nhận được');
            $table->text('signature')->nullable()->comment('Chữ ký nhận được từ gateway');
            $table->text('message')->nullable()->comment('Thông điệp xử lý');
            $table->timestamps();

            $table->index(['provider', 'booking_code']);
            $table->index('booking_id');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipn_logs');
    }
};
