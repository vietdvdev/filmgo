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
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('name', 100)->comment('Tên quy tắc giá');
            $table->tinyInteger('day_of_week')->comment('0: CN, 1-6: T2-T7');
            $table->time('start_time')->comment('Giờ bắt đầu áp dụng khung giá');
            $table->time('end_time')->comment('Giờ kết thúc khung giá');
            $table->integer('adjustment_amount')->default(0)->comment('Số tiền cộng/trừ vào base_price (VNĐ)');
            $table->tinyInteger('is_active')->default(1)->comment('Quy tắc đang được áp dụng (1=Có, 0=Không)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
