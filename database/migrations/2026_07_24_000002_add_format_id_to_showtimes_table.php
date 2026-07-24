<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm cột format_id vào bảng showtimes đứng ngay sau movie_id
        Schema::table('showtimes', function (Blueprint $table) {
            $table->foreignId('format_id')
                ->nullable()
                ->after('movie_id')
                ->comment('Định dạng chiếu của suất này (2D, 3D, IMAX...)')
                ->constrained('formats')
                ->cascadeOnDelete();
        });

        // 2. Khởi tạo dữ liệu định dạng mặc định '2D' nếu chưa có và gán cho các suất chiếu cũ
        $defaultFormatId = DB::table('formats')->where('name', '2D')->value('id');
        if (!$defaultFormatId) {
            $defaultFormatId = DB::table('formats')->insertGetId([
                'name'            => '2D',
                'surcharge_price' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        DB::table('showtimes')->whereNull('format_id')->update(['format_id' => $defaultFormatId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropForeign(['format_id']);
            $table->dropColumn('format_id');
        });
    }
};
