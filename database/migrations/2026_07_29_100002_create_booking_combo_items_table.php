<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng booking_combo_items để lưu đồ ăn/thức uống lẻ từng món
     * (không theo gói combo) trong một đơn hàng.
     */
    public function up(): void
    {
        Schema::create('booking_combo_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id')
                  ->comment('Đơn hàng chứa món này');

            $table->unsignedBigInteger('combo_item_id')
                  ->comment('Thành phần đồ ăn/uống đơn lẻ');

            $table->unsignedSmallInteger('quantity')
                  ->default(1)
                  ->comment('Số lượng');

            $table->unsignedInteger('unit_price')
                  ->comment('Đơn giá tại thời điểm mua (snapshot)');

            $table->unsignedInteger('subtotal')
                  ->comment('Thành tiền = quantity × unit_price');

            $table->timestamps();

            // Foreign keys
            $table->foreign('booking_id')
                  ->references('id')
                  ->on('bookings')
                  ->cascadeOnDelete();

            $table->foreign('combo_item_id')
                  ->references('id')
                  ->on('combo_items')
                  ->restrictOnDelete();

            // Index
            $table->index('booking_id');
        });
    }

    /**
     * Rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_combo_items');
    }
};
