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
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('movie_id')->comment('Phim được chiếu')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('room_id')->comment('Phòng chiếu')->constrained('rooms')->cascadeOnDelete();
            $table->date('show_date')->comment('Ngày chiếu cụ thể');
            $table->time('start_time')->comment('Giờ bắt đầu chiếu');
            $table->time('end_time')->comment('Giờ kết thúc');
            $table->integer('base_price')->comment('Giá vé cơ sở của suất (VNĐ)');
            $table->enum('status', ['upcoming', 'showing', 'finished', 'cancelled'])->default('upcoming')->comment('Trạng thái suất chiếu');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm - hủy suất chiếu');

            $table->index(['movie_id', 'show_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
