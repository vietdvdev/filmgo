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
        Schema::create('user_cinemas', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('user_id')->comment('Mã quản lý/nhân viên')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cinema_id')->comment('Rạp phim được phân quyền')->constrained('cinemas')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời gian phân công');
            $table->unique(['user_id', 'cinema_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cinemas');
    }
};
