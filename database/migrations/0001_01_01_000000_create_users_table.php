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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('Khóa chính hệ thống');
            $table->string('full_name')->comment('Họ và tên người dùng');
            $table->string('email')->unique()->comment('Địa chỉ email đăng nhập');
            $table->string('phone', 20)->nullable()->comment('Số điện thoại liên hệ');
            $table->string('password')->comment('Mật khẩu đã được mã hóa (bcrypt)');
            $table->string('avatar')->nullable()->comment('Đường dẫn ảnh đại diện');
            $table->timestamp('email_verified_at')->nullable()->comment('Thời điểm xác thực email');
            $table->enum('status', ['active', 'locked'])->default('active')->comment('Trạng thái tài khoản');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm phục vụ audit');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Email yêu cầu');
            $table->string('token')->comment('Chuỗi token bí mật');
            $table->timestamp('created_at')->nullable()->comment('Thời gian tạo token');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
