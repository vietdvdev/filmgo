<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('printed_at')->nullable()->after('expired_at')->comment('Thời điểm đơn hàng đã được in vé hoặc biên lai tại quầy');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('printed_at');
        });
    }
};
