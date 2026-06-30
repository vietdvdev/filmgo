<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 3 — Tối ưu bảng promotions
 *
 * Thay đổi:
 * 1. RENAME cột quantity → usage_limit (tên rõ nghĩa hơn)
 * 2. THÊM cột used_count — đếm số lần mã đã thực tế được dùng
 * 3. THÊM cột apply_to — phạm vi áp dụng (all / ticket_only / combo_only)
 * 4. THÊM cột max_discount_amount — trần tiền giảm tối đa
 *    (VD: Giảm 20% nhưng tối đa 50k → không lỗ khi đơn lớn)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bước 1: Rename quantity → usage_limit ───────────────────────────────
        // MySQL 8+: dùng RENAME COLUMN trực tiếp
        DB::statement('ALTER TABLE promotions RENAME COLUMN `quantity` TO `usage_limit`');

        // Cập nhật comment cho cột vừa rename
        DB::statement("
            ALTER TABLE promotions
            MODIFY COLUMN `usage_limit` INT UNSIGNED NULL
            COMMENT 'Tổng số lượt sử dụng tối đa (NULL = vô hạn). Đổi tên từ quantity.'
        ");

        // ── Bước 2: Thêm các cột mới ────────────────────────────────────────────
        Schema::table('promotions', function (Blueprint $table) {
            // Số lần đã được dùng thực tế (đối chiếu với usage_limit)
            $table->unsignedInteger('used_count')
                ->default(0)
                ->after('usage_limit')
                ->comment('Số lần mã đã được sử dụng thực tế. Dùng để so sánh với usage_limit.');

            // Phạm vi áp dụng: toàn đơn / chỉ vé / chỉ combo
            $table->enum('apply_to', ['all', 'ticket_only', 'combo_only'])
                ->default('all')
                ->after('code')
                ->comment('Phạm vi áp dụng mã: all=cả đơn, ticket_only=chỉ vé, combo_only=chỉ F&B/Combo');

            // Trần tiền giảm (quan trọng khi discount_type = percent)
            $table->unsignedInteger('max_discount_amount')
                ->nullable()
                ->after('discount_value')
                ->comment('Số tiền giảm tối đa (VNĐ). VD: Giảm 20% nhưng không quá 50.000đ. NULL = không giới hạn.');
        });
    }

    public function down(): void
    {
        // ── Xóa các cột mới thêm ────────────────────────────────────────────────
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['used_count', 'apply_to', 'max_discount_amount']);
        });

        // ── Rename usage_limit → quantity (rollback) ────────────────────────────
        DB::statement('ALTER TABLE promotions RENAME COLUMN `usage_limit` TO `quantity`');
    }
};
