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
        Schema::create('actors', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('name', 255)->comment('Tên diễn viên');
            $table->string('avatar', 255)->nullable()->comment('Ảnh đại diện diễn viên');
            $table->text('biography')->nullable()->comment('Tiểu sử, sự nghiệp');
            $table->date('date_of_birth')->nullable()->comment('Ngày sinh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actors');
    }
};
