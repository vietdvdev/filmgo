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
        Schema::create('cinemas', function (Blueprint $table) {
            $table->id()->comment('Mã chi nhánh');
            $table->string('name', 255)->comment('Tên rạp (VD: CGV Vincom)');
            $table->string('address', 255)->comment('Địa chỉ cụ thể');
            $table->string('phone', 20)->nullable()->comment('Hotline rạp');
            $table->string('city', 100)->comment('Thành phố');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái hoạt động');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinemas');
    }
};
