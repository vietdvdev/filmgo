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
        Schema::create('seats', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('room_id')->comment('Thuộc phòng chiếu nào')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('seat_type_id')->comment('Loại ghế của vị trí này')->constrained('seat_types')->restrictOnDelete();
            $table->string('seat_row', 10)->comment('Ký hiệu hàng ghế (A, B, C...)');
            $table->integer('seat_number')->comment('Số thứ tự ghế trên hàng (1, 2, 3...)');
            $table->enum('status', ['active', 'maintenance'])->default('active')->comment('Trạng thái vật lý của ghế');
            $table->unique(['room_id', 'seat_row', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
