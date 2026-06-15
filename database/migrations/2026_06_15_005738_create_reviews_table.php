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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('user_id')->comment('Khách hàng viết đánh giá')->constrained('users')->cascadeOnDelete();
            $table->foreignId('movie_id')->comment('Phim được đánh giá')->constrained('movies')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('Điểm số từ 1 đến 5 sao');
            $table->text('comment')->nullable()->comment('Nội dung bình luận');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->comment('Trạng thái kiểm duyệt nội dung');
            $table->timestamp('created_at')->nullable()->comment('Thời gian viết đánh giá');
            $table->softDeletes()->comment('Xóa mềm nếu vi phạm nội dung');

            $table->unique(['user_id', 'movie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
