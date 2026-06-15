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
        Schema::create('movies', function (Blueprint $table) {
            $table->id()->comment('Mã phim');
            $table->string('title', 255)->comment('Tên bộ phim');
            $table->string('slug', 255)->unique()->comment('Đường dẫn SEO thân thiện');
            $table->string('poster', 255)->nullable()->comment('Ảnh poster chính');
            $table->string('trailer_url', 255)->nullable()->comment('Liên kết trailer YouTube/Vimeo');
            $table->integer('duration')->comment('Thời lượng phim (phút)');
            $table->date('release_date')->comment('Ngày khởi chiếu chính thức');
            $table->string('director', 255)->nullable()->comment('Tên đạo diễn');
            $table->string('country', 100)->nullable()->comment('Quốc gia sản xuất');
            $table->enum('age_limit', ['P', 'K', 'T13', 'T16', 'T18'])->comment('Phân loại độ tuổi');
            $table->text('description')->nullable()->comment('Mô tả nội dung phim');
            $table->enum('status', ['upcoming', 'showing', 'stopped'])->default('upcoming')->comment('Trạng thái phim');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm - phục hồi được');

            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
