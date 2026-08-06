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
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Khách hàng');
            $table->integer('amount')->comment('Số điểm giao dịch (có thể âm nếu đổi điểm)');
            $table->enum('type', ['earn', 'redeem'])->comment('Loại giao dịch: earn (tích điểm), redeem (đổi điểm)');
            $table->string('description')->nullable()->comment('Mô tả giao dịch (ví dụ: Mua vé xem phim #123)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
