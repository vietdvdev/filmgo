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
        // Thêm cột format_id vào bảng movies để lưu duy nhất 1 định dạng chiếu cho mỗi phim
        Schema::table('movies', function (Blueprint $table) {
            $table->unsignedBigInteger('format_id')->nullable()->after('status');
            $table->foreign('format_id')->references('id')->on('formats')->onDelete('set null');
        });

        // Xóa bảng trung gian movie_formats vì không còn sử dụng quan hệ nhiều-nhiều
        Schema::dropIfExists('movie_formats');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tạo lại bảng trung gian movie_formats nếu rollback
        Schema::create('movie_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->foreignId('format_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Xóa khóa ngoại và cột format_id trong bảng movies
        Schema::table('movies', function (Blueprint $table) {
            $table->dropForeign(['format_id']);
            $table->dropColumn('format_id');
        });
    }
};
