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
        Schema::create('showtime_seats', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('showtime_id')->comment('Thuộc suất chiếu nào')->constrained('showtimes')->cascadeOnDelete();
            $table->foreignId('seat_id')->comment('Vị trí ghế vật lý tương ứng')->constrained('seats')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->comment('Khách đang giữ hoặc đặt ghế')->constrained('users')->nullOnDelete();
            $table->enum('status', ['available', 'holding', 'booked'])->default('available')->comment('Trạng thái ghế thực tế');
            $table->timestamp('locked_at')->nullable()->comment('Thời điểm bắt đầu giữ ghế (holding)');
            $table->timestamp('expires_at')->nullable()->comment('Hạn giữ ghế - tự động nhả khi quá hạn');

            $table->unique(['showtime_id', 'seat_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtime_seats');
    }
};
