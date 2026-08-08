<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đổi cột room_type từ ENUM('2D','3D','IMAX','4DX') sang VARCHAR(50).
     *
     * Lý do: Admin có thể tạo thêm định dạng mới (VD: 8D, Dolby, ...).
     * ENUM cứng không thể chứa các giá trị mới. VARCHAR linh hoạt hơn.
     */
    public function up(): void
    {
        // MySQL không hỗ trợ thay đổi trực tiếp ENUM → VARCHAR bằng change() chuẩn.
        // Dùng raw SQL ALTER TABLE để đổi kiểu cột an toàn.
        DB::statement("ALTER TABLE `rooms` MODIFY COLUMN `room_type` VARCHAR(50) NOT NULL DEFAULT '2D' COMMENT 'Loại phòng chiếu (tên định dạng)'");
    }

    /**
     * Rollback: khôi phục về ENUM gốc (chỉ giữ 4 giá trị cũ).
     */
    public function down(): void
    {
        // Trước khi rollback, cần đảm bảo dữ liệu hiện tại trong ENUM hợp lệ.
        DB::statement("UPDATE `rooms` SET `room_type` = '2D' WHERE `room_type` NOT IN ('2D','3D','IMAX','4DX')");
        DB::statement("ALTER TABLE `rooms` MODIFY COLUMN `room_type` ENUM('2D','3D','IMAX','4DX') NOT NULL DEFAULT '2D' COMMENT 'Loại phòng chiếu'");
    }
};
