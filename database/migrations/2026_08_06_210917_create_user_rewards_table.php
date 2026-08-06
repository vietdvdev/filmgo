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
        Schema::create('user_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Khách hàng');
            $table->foreignId('reward_id')->constrained('rewards')->onDelete('cascade')->comment('Quà tặng đã đổi');
            $table->string('code')->unique()->comment('Mã voucher duy nhất');
            $table->boolean('is_used')->default(false)->comment('Trạng thái sử dụng');
            $table->timestamp('used_at')->nullable()->comment('Thời gian sử dụng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rewards');
    }
};
