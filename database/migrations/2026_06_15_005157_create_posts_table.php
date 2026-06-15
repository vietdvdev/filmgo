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
        Schema::create('posts', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('author_id')->comment('Admin/Staff viết bài')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255)->comment('Tiêu đề bài viết');
            $table->string('slug', 255)->unique()->comment('Đường dẫn SEO');
            $table->string('thumbnail', 255)->nullable()->comment('Ảnh bìa bài viết');
            $table->longText('content')->comment('Nội dung đầy đủ (HTML)');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->comment('Trạng thái xuất bản');
            $table->timestamp('published_at')->nullable()->comment('Thời gian xuất bản');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
