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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('user_id')->comment('Người nhận thông báo')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->comment('Loại: booking_confirmed, check_in_reminder...');
            $table->string('title', 255)->comment('Tiêu đề thông báo');
            $table->text('content')->comment('Nội dung chi tiết');
            $table->tinyInteger('is_read')->default(0)->comment('Đã đọc chưa (0: Chưa, 1: Rồi)');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm gửi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
