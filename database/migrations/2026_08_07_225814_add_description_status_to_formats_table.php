<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formats', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name')->comment('Mô tả định dạng');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('surcharge_price')->comment('Trạng thái hoạt động');
        });
    }

    public function down(): void
    {
        Schema::table('formats', function (Blueprint $table) {
            $table->dropColumn(['description', 'status']);
        });
    }
};
