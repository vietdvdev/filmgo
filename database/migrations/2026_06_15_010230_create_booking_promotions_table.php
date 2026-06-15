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
        Schema::create('booking_promotions', function (Blueprint $table) {
            $table->foreignId('booking_id')->comment('Đơn hàng được áp mã')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('promotion_id')->comment('Mã khuyến mãi được sử dụng')->constrained('promotions')->cascadeOnDelete();
            $table->primary(['booking_id', 'promotion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_promotions');
    }
};
