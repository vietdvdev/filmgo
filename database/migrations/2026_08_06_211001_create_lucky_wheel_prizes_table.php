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
        Schema::create('lucky_wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên giải thưởng (VD: 50 Điểm, Voucher 20k)');
            $table->enum('type', ['points', 'reward'])->comment('Loại giải thưởng: points (điểm), reward (quà tặng)');
            $table->integer('value')->comment('Giá trị: Số điểm hoặc ID của reward');
            $table->decimal('probability', 5, 2)->comment('Tỉ lệ trúng phần trăm (VD: 10.50)');
            $table->integer('quantity')->default(0)->comment('Số lượng phần thưởng (0 là vô hạn đối với điểm)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_prizes');
    }
};
