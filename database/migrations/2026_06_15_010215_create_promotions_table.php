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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id()->comment('Khóa chính');
            $table->string('code', 50)->unique()->comment('Mã nhập của khách (VD: SALE50)');
            $table->enum('discount_type', ['percent', 'fixed'])->comment('Loại giảm: % hoặc tiền');
            $table->integer('discount_value')->comment('Giá trị giảm');
            $table->integer('min_order_amount')->default(0)->comment('Giá trị đơn tối thiểu để áp dụng mã');
            $table->integer('max_uses_per_user')->default(1)->comment('Số lần tối đa một user được dùng');
            $table->timestamp('start_date')->comment('Ngày bắt đầu áp dụng');
            $table->timestamp('end_date')->comment('Ngày kết thúc áp dụng');
            $table->integer('quantity')->nullable()->comment('Tổng lượt sử dụng (Null = vô hạn)');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái mã');
            $table->softDeletes()->comment('Xóa mềm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
