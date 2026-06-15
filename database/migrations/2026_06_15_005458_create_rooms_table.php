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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('cinema_id')->comment('Thuộc rạp nào')->constrained('cinemas')->cascadeOnDelete();
            $table->string('room_name', 100)->comment('Tên phòng (VD: Phòng 1, Hall A)');
            $table->integer('capacity')->comment('Tổng sức chứa (số ghế)');
            $table->enum('room_type', ['2D', '3D', 'IMAX', '4DX'])->comment('Loại phòng chiếu');
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active')->comment('Trạng thái phòng');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
