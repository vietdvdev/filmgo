<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm các Database Index để tối ưu tốc độ truy vấn cho các cột thường xuyên được dùng
     * trong mệnh đề WHERE, JOIN và ORDER BY.
     *
     * Danh sách index được thêm:
     * - bookings.payment_status      : Filter đơn hàng theo trạng thái thanh toán (rất thường dùng)
     * - bookings.booking_status      : Filter đơn hàng theo trạng thái booking
     * - bookings.channel             : Phân tách doanh thu online vs counter (dùng trong KPI)
     * - bookings.expired_at          : Cron job xóa đơn hết hạn dùng cột này để filter
     * - showtime_seats.expires_at    : Cron job giải phóng ghế hết hạn holding/locked
     * - showtime_seats.status        : Filter trạng thái ghế (available, booked, holding)
     * - ipn_logs.booking_id          : JOIN để tìm log của đơn hàng cụ thể
     * - ipn_logs.provider            : Filter log theo cổng thanh toán (vnpay, momo)
     * - showtimes.show_date + status : Composite index cho query lịch chiếu theo ngày
     */
    public function up(): void
    {
        // ── Index cho bảng bookings ─────────────────────────────────
        Schema::table('bookings', function (Blueprint $table) {
            // Kiểm tra trước khi thêm để tránh lỗi nếu index đã tồn tại
            if (!$this->indexExists('bookings', 'bookings_payment_status_index')) {
                // Index payment_status — dùng trong hầu hết query thống kê
                $table->index('payment_status', 'bookings_payment_status_index');
            }

            if (!$this->indexExists('bookings', 'bookings_booking_status_index')) {
                // Index booking_status — dùng khi lọc đơn confirmed/cancelled
                $table->index('booking_status', 'bookings_booking_status_index');
            }

            if (!$this->indexExists('bookings', 'bookings_channel_index')) {
                // Index channel — dùng trong DashboardService khi groupBy channel
                $table->index('channel', 'bookings_channel_index');
            }

            if (!$this->indexExists('bookings', 'bookings_expired_at_index')) {
                // Index expired_at — dùng trong Cron job ExpireBookings
                $table->index('expired_at', 'bookings_expired_at_index');
            }

            if (!$this->indexExists('bookings', 'bookings_created_at_index')) {
                // Index created_at — dùng trong hầu hết query thống kê khoảng thời gian
                $table->index('created_at', 'bookings_created_at_index');
            }
        });

        // ── Index cho bảng showtime_seats ───────────────────────────
        Schema::table('showtime_seats', function (Blueprint $table) {
            if (!$this->indexExists('showtime_seats', 'showtime_seats_expires_at_index')) {
                // Index expires_at — dùng trong Cron job giải phóng ghế hết hạn
                $table->index('expires_at', 'showtime_seats_expires_at_index');
            }

            if (!$this->indexExists('showtime_seats', 'showtime_seats_status_index')) {
                // Index status — dùng khi filter available/booked/holding
                $table->index('status', 'showtime_seats_status_index');
            }
        });

        // ── Index cho bảng ipn_logs ─────────────────────────────────
        Schema::table('ipn_logs', function (Blueprint $table) {
            if (!$this->indexExists('ipn_logs', 'ipn_logs_booking_id_index')) {
                // Index booking_id — dùng khi tìm log của một đơn hàng cụ thể
                $table->index('booking_id', 'ipn_logs_booking_id_index');
            }

            if (!$this->indexExists('ipn_logs', 'ipn_logs_provider_index')) {
                // Index provider — dùng khi filter log theo cổng thanh toán
                $table->index('provider', 'ipn_logs_provider_index');
            }
        });

        // ── Composite Index cho bảng showtimes ─────────────────────
        Schema::table('showtimes', function (Blueprint $table) {
            if (!$this->indexExists('showtimes', 'showtimes_show_date_status_index')) {
                /**
                 * Composite index (show_date, status) cho query lấy suất chiếu theo ngày.
                 * MySQL sẽ dùng index này khi WHERE show_date = ? AND status != 'cancelled'.
                 * Thứ tự cột quan trọng: cột có cardinality cao hơn (show_date) đặt trước.
                 */
                $table->index(['show_date', 'status'], 'showtimes_show_date_status_index');
            }
        });
    }

    /**
     * Xóa các index đã thêm khi rollback migration.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndexIfExists('bookings_payment_status_index');
            $table->dropIndexIfExists('bookings_booking_status_index');
            $table->dropIndexIfExists('bookings_channel_index');
            $table->dropIndexIfExists('bookings_expired_at_index');
            $table->dropIndexIfExists('bookings_created_at_index');
        });

        Schema::table('showtime_seats', function (Blueprint $table) {
            $table->dropIndexIfExists('showtime_seats_expires_at_index');
            $table->dropIndexIfExists('showtime_seats_status_index');
        });

        Schema::table('ipn_logs', function (Blueprint $table) {
            $table->dropIndexIfExists('ipn_logs_booking_id_index');
            $table->dropIndexIfExists('ipn_logs_provider_index');
        });

        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropIndexIfExists('showtimes_show_date_status_index');
        });
    }

    /**
     * Kiểm tra xem một index có tồn tại hay chưa để tránh lỗi khi chạy lại migration.
     *
     * @param  string  $table  Tên bảng
     * @param  string  $index  Tên index
     * @return bool
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$index]
        );

        return !empty($indexes);
    }
};
