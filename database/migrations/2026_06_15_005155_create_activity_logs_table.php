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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->foreignId('user_id')->comment('Tài khoản thực hiện thao tác')->constrained('users')->cascadeOnDelete();
            $table->string('action', 100)->comment('Tên hành động');
            $table->string('model_type', 100)->nullable()->comment('Loại đối tượng bị tác động');
            $table->bigInteger('model_id')->nullable()->comment('ID đối tượng bị tác động');
            $table->text('description')->nullable()->comment('Mô tả chi tiết hành động');
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IP khi thực hiện');
            $table->timestamp('created_at')->nullable()->comment('Thời gian ghi log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
