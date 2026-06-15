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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('booking_id')->comment('Mã đơn đặt vé')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('showtime_seat_id')->comment('Tham chiếu trạng thái ghế của suất chiếu')->constrained('showtime_seats')->cascadeOnDelete();
            $table->integer('price')->comment('Giá chốt tại thời điểm mua - Price Snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
