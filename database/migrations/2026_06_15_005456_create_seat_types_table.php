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
        Schema::create('seat_types', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('name', 50)->unique()->comment('Tên loại ghế: Thường, VIP, Sweetbox');
            $table->integer('surcharge_price')->default(0)->comment('Giá phụ thu cộng thêm so với base_price (VNĐ)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_types');
    }
};
