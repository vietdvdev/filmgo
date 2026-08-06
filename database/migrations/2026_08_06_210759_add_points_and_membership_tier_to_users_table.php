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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('points')->default(0)->after('remember_token')->comment('Điểm thưởng hiện tại của người dùng');
            $table->enum('membership_tier', ['Bạc', 'Vàng', 'Kim Cương'])->default('Bạc')->after('points')->comment('Hạng thành viên: Bạc, Vàng, Kim Cương');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points', 'membership_tier']);
        });
    }
};
