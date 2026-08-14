<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo lại bảng pivot movie_formats (nhiều-nhiều)
        Schema::create('movie_formats', function (Blueprint $table) {
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('format_id')->constrained('formats')->cascadeOnDelete();
            $table->primary(['movie_id', 'format_id']);
        });

        // Di chuyển dữ liệu format_id hiện có sang bảng pivot
        DB::statement('INSERT INTO movie_formats (movie_id, format_id)
            SELECT id, format_id FROM movies WHERE format_id IS NOT NULL');

        // Xóa cột format_id khỏi bảng movies
        Schema::table('movies', function (Blueprint $table) {
            $table->dropForeign(['format_id']);
            $table->dropColumn('format_id');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->unsignedBigInteger('format_id')->nullable()->after('status');
            $table->foreign('format_id')->references('id')->on('formats')->onDelete('set null');
        });

        // Lấy format đầu tiên của mỗi phim trả về format_id
        DB::statement('UPDATE movies m
            JOIN movie_formats mf ON mf.movie_id = m.id
            SET m.format_id = mf.format_id
            WHERE m.format_id IS NULL');

        Schema::dropIfExists('movie_formats');
    }
};
