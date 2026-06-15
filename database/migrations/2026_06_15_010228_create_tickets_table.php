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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('booking_detail_id')->comment('Ghế tương ứng trên chi tiết đơn')->constrained('booking_details')->cascadeOnDelete();
            $table->string('qr_code', 255)->unique()->comment('Chuỗi định danh để mã hóa QR');
            $table->enum('ticket_status', ['unused', 'used', 'cancelled'])->default('unused')->comment('Trạng thái vé');
            $table->timestamp('checked_in_at')->nullable()->comment('Thời gian quét mã vào rạp');

            $table->index(['qr_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
