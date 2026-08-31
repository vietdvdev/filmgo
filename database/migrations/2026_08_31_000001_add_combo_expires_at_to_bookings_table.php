<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Them cot combo_expires_at vao bang bookings de luu han su dung
     * rieng cho don hang bap nuoc/combo_only (3 ngay sau khi thanh toan).
     * Tach biet hoan toan voi cot expired_at (dung de giu ghe tam thoi).
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('combo_expires_at')
                  ->nullable()
                  ->after('expired_at')
                  ->comment('Han su dung don bap nuoc combo_only (3 ngay sau khi thanh toan)');
        });
    }

    /**
     * Rollback.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('combo_expires_at');
        });
    }
};
