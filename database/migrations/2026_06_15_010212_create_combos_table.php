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
        Schema::create('combos', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('combo_name', 255)->comment('Tên combo');
            $table->string('image', 255)->nullable()->comment('Hình ảnh minh họa combo');
            $table->integer('price')->comment('Giá bán (VNĐ)');
            $table->text('description')->nullable()->comment('Thành phần chi tiết combo');
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
        Schema::dropIfExists('combos');
    }
};
