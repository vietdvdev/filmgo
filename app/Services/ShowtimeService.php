<?php

namespace App\Services;

use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShowtimeService
{
    /**
     * Lấy và xử lý danh sách suất chiếu của một bộ phim theo thời gian thực cho Khách hàng.
     *
     * Tối ưu: Thay vì gọi $showtime->saveQuietly() trong vòng lặp transform (N+1 UPDATE query),
     * chúng ta thu thập các ID cần update vào mảng, sau đó thực hiện bulk update một lần duy nhất.
     *
     * @param int $movieId
     * @return Collection
     */
    public function getCustomerShowtimesForMovie(int $movieId): Collection
    {
        $now = now();
        $todayStr = today()->toDateString();

        /**
         * Lấy suất chiếu kèm room.cinema để phục vụ groupBy rạp ở controller.
         * Không load 'movie' vì movie đã được load ở controller và không cần duration ở đây.
         */
        $showtimes = Showtime::where('movie_id', $movieId)
            ->whereDate('show_date', '>=', $todayStr)
            ->where('status', '!=', 'cancelled')
            ->with(['room', 'room.cinema'])
            ->orderBy('start_time')
            ->get();

        // Mảng collect các ID cần cập nhật theo status mới — để bulk update sau
        $toUpdateFinished = [];
        $toUpdateShowing  = [];

        // Xử lý cập nhật status và gắn thuộc tính hiển thị theo thời gian thực
        $showtimes->transform(function ($showtime) use ($now, &$toUpdateFinished, &$toUpdateShowing) {
            $showDateStr = $showtime->show_date ? $showtime->show_date->format('Y-m-d') : today()->toDateString();
            $startDateTime = Carbon::parse($showDateStr . ' ' . $showtime->start_time);

            if ($showtime->end_time) {
                $endDateTime = Carbon::parse($showDateStr . ' ' . $showtime->end_time);
            } else {
                // Fallback: dùng duration mặc định nếu không có end_time (dữ liệu cũ)
                $endDateTime = $startDateTime->copy()->addMinutes(120);
            }

            $oldStatus = $showtime->status;

            if ($now->gte($endDateTime)) {
                $realTimeStatus = 'finished'; // Đã chiếu xong
            } elseif ($now->gte($startDateTime)) {
                $realTimeStatus = 'showing';  // Đang chiếu
            } elseif ($showtime->status === 'upcoming' && $showtime->publish_at && $showtime->publish_at->gt($now)) {
                $realTimeStatus = 'upcoming'; // Sắp mở bán
            } else {
                $realTimeStatus = 'active';   // Mở bán / Đặt vé
            }

            /**
             * TRÁNH N+1 UPDATE: Thay vì saveQuietly() mỗi vòng lặp,
             * chỉ thu thập ID vào mảng. Bulk UPDATE sẽ được thực hiện sau vòng lặp.
             */
            if (in_array($realTimeStatus, ['finished', 'showing']) && $oldStatus !== $realTimeStatus && $oldStatus !== 'cancelled') {
                // Cập nhật thuộc tính model ngay để hiển thị đúng
                $showtime->status = $realTimeStatus;

                // Thu thập ID để bulk update
                if ($realTimeStatus === 'finished') {
                    $toUpdateFinished[] = $showtime->id;
                } else {
                    $toUpdateShowing[] = $showtime->id;
                }
            }

            // Gắn các thông tin phụ trợ phục vụ hiển thị ở frontend customer
            $showtime->real_time_status = $realTimeStatus;

            // WARN-03 FIX: is_bookable phải đồng nhất với validateBookable().
            // validateBookable() từ chối cả 'showing' (đang chiếu) lẫn 'finished'.
            // Trước đây chỉ check === 'active', khiến nút "Đặt vé" hiện cho suất đang chiếu.
            $showtime->is_bookable = ($realTimeStatus === 'active');

            switch ($realTimeStatus) {
                case 'finished':
                    $showtime->status_label = 'Đã chiếu';
                    $showtime->badge_class = 'bg-slate-200 text-slate-600 border-slate-300';
                    break;
                case 'showing':
                    $showtime->status_label = 'Đang chiếu';
                    $showtime->badge_class = 'bg-amber-100 text-amber-800 border-amber-300 font-bold';
                    break;
                case 'upcoming':
                    $showtime->status_label = 'Sắp mở bán';
                    $showtime->badge_class = 'bg-blue-100 text-blue-800 border-blue-300 font-bold';
                    break;
                default:
                    $showtime->status_label = 'Mở bán';
                    $showtime->badge_class = 'bg-brand-primary text-white border-brand-primary font-bold';
                    break;
            }

            return $showtime;
        });

        /**
         * BULK UPDATE: Thực hiện tối đa 2 câu UPDATE thay vì N câu saveQuietly().
         * Dùng whereIn() để cập nhật hàng loạt ID trong một lần truy vấn duy nhất.
         */
        if (!empty($toUpdateFinished)) {
            Showtime::whereIn('id', $toUpdateFinished)->update(['status' => 'finished']);
        }
        if (!empty($toUpdateShowing)) {
            Showtime::whereIn('id', $toUpdateShowing)->update(['status' => 'showing']);
        }

        return $showtimes;
    }

    /**
     * Kiểm tra suất chiếu có thể đặt vé được hay không.
     *
     * @param Showtime $showtime
     * @return array
     */
    public function validateBookable(Showtime $showtime): array
    {
        $now = now();
        $showDateStr = $showtime->show_date ? $showtime->show_date->format('Y-m-d') : today()->toDateString();
        $startDateTime = Carbon::parse($showDateStr . ' ' . $showtime->start_time);

        if ($showtime->end_time) {
            $endDateTime = Carbon::parse($showDateStr . ' ' . $showtime->end_time);
        } else {
            $duration = $showtime->movie ? ($showtime->movie->duration ?? 120) : 120;
            $endDateTime = $startDateTime->copy()->addMinutes($duration);
        }

        if ($showtime->status === 'cancelled') {
            return ['bookable' => false, 'message' => 'Suất chiếu này đã bị hủy.'];
        }

        if ($now->gte($endDateTime) || $showtime->status === 'finished') {
            return ['bookable' => false, 'message' => 'Suất chiếu này đã kết thúc, không thể đặt vé.'];
        }

        if ($now->gte($startDateTime) || $showtime->status === 'showing') {
            return ['bookable' => false, 'message' => 'Suất chiếu này đang chiếu, không thể đặt vé online.'];
        }

        if ($showtime->status === 'upcoming' && $showtime->publish_at && $showtime->publish_at->gt($now)) {
            return ['bookable' => false, 'message' => 'Suất chiếu này chưa mở bán vé.'];
        }

        return ['bookable' => true, 'message' => ''];
    }
}
