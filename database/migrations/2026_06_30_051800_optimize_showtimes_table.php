<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 1 — Tối ưu bảng showtimes
 *
 * Thay đổi:
 * 1. Mở rộng enum status: thêm 'active', 'running', 'ended' (giữ nguyên 'showing', 'finished')
 *    Tập hợp mới: ['upcoming', 'active', 'showing', 'running', 'finished', 'ended', 'cancelled']
 *    - upcoming: chờ mở bán
 *    - active:   đang mở bán (dùng từ nay về sau, tương đương showing cũ)
 *    - showing:  đang mở bán (legacy — giữ nguyên để không vỡ dữ liệu cũ)
 *    - running:  đang chiếu  (dùng từ nay về sau, tương đương finished cũ)
 *    - finished: đã kết thúc (legacy — giữ nguyên)
 *    - ended:    đã kết thúc (chuẩn mới)
 *    - cancelled: đã hủy
 *
 * 2. Thêm cột is_auto_generated (boolean) — đánh dấu suất do hệ thống auto tạo
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bước 1: Mở rộng enum status ────────────────────────────────────────
        DB::statement("
            ALTER TABLE showtimes
            MODIFY COLUMN status
            ENUM('upcoming', 'active', 'showing', 'running', 'finished', 'ended', 'cancelled')
            NOT NULL
            DEFAULT 'upcoming'
            COMMENT 'Vòng đời suất chiếu: upcoming=chờ, active/showing=mở bán, running=đang chiếu, finished/ended=kết thúc, cancelled=hủy'
        ");

        // ── Bước 2: Thêm cột is_auto_generated ─────────────────────────────────
        Schema::table('showtimes', function (Blueprint $table) {
            $table->boolean('is_auto_generated')
                ->default(false)
                ->after('status')
                ->comment('true = hệ thống tự tạo (bulk auto), false = manager tạo bằng tay');
        });
    }

    public function down(): void
    {
        // ── Khôi phục enum về trạng thái cũ ────────────────────────────────────
        DB::statement("
            ALTER TABLE showtimes
            MODIFY COLUMN status
            ENUM('upcoming', 'showing', 'finished', 'cancelled')
            NOT NULL
            DEFAULT 'upcoming'
        ");

        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropColumn('is_auto_generated');
        });
    }
};
