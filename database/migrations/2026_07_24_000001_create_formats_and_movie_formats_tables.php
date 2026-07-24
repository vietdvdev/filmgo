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
        // 1. Bảng lưu danh sách các Định dạng chiếu phim (2D, 3D, IMAX, 4DX,...)
        Schema::create('formats', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('name', 50)->unique()->comment('Tên định dạng (Ví dụ: 2D, 3D, IMAX, 4DX)');
            $table->integer('surcharge_price')->default(0)->comment('Phụ thu giá vé cho định dạng này (VNĐ)');
            $table->timestamps();
        });

        // 2. Bảng pivot quản lý quan hệ Nhiều - Nhiều giữa Phim (movies) và Định dạng (formats)
        Schema::create('movie_formats', function (Blueprint $table) {
            $table->foreignId('movie_id')->comment('Mã phim')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('format_id')->comment('Mã định dạng')->constrained('formats')->cascadeOnDelete();
            $table->primary(['movie_id', 'format_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_formats');
        Schema::dropIfExists('formats');
    }
};
