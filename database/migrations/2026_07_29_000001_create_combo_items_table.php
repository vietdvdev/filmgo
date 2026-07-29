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
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id()->comment('Khóa chính thành phần combo');
            $table->string('name', 255)->comment('Tên thành phần (VD: Bắp lớn, Bắp nhỏ, Nước lớn, Nước nhỏ)');
            $table->string('type', 50)->default('other')->comment('Loại thành phần: popcorn, drink, snack, other');
            $table->string('unit', 50)->default('Phần')->comment('Đơn vị tính (Hộp, Ly, Gói...)');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái kinh doanh');
            $table->timestamps();
            $table->softDeletes()->comment('Xóa mềm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_items');
    }
};
