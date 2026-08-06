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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên quà tặng (VD: Voucher 50k, Bắp nước miễn phí)');
            $table->text('description')->nullable()->comment('Mô tả quà tặng');
            $table->integer('points_required')->comment('Số điểm cần để đổi');
            $table->integer('quantity')->default(0)->comment('Số lượng quà tặng còn lại');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
