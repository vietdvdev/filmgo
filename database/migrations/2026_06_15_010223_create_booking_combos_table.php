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
        Schema::create('booking_combos', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('booking_id')->comment('Đơn đặt vé tương ứng')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('combo_id')->comment('Mã combo đã chọn')->constrained('combos')->restrictOnDelete();
            $table->integer('quantity')->comment('Số lượng đặt mua');
            $table->integer('subtotal')->comment('Thành tiền - Snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_combos');
    }
};
