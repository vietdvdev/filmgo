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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('name', 100)->comment('Tên ngày lễ (VD: Tet Nguyen Dan, Quoc Khanh)');
            $table->date('holiday_date')->unique()->comment('Tra cứu ngày lễ không trùng lặp');
            $table->string('description', 255)->nullable()->comment('Ghi chú thêm về ngày lễ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
