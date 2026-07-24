<?php

namespace App\Services;

use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShowtimeService
{
    /**
     * Lấy và xử lý danh sách suất chiếu của một bộ phim theo thời gian thực cho Khách hàng.
     *
     * @param int $movieId
     * @return Collection
     */
    public function getCustomerShowtimesForMovie(int $movieId): Collection
    {
        $now = now();
        $todayStr = today()->toDateString();

        // Lấy tất cả các suất chiếu từ hôm nay trở đi ngoại trừ suất đã bị hủy (cancelled)
        $showtimes = Showtime::where('movie_id', $movieId)
            ->whereDate('show_date', '>=', $todayStr)
            ->where('status', '!=', 'cancelled')
            ->with(['movie', 'room', 'room.cinema'])
            ->orderBy('start_time')
            ->get();

        // Xử lý cập nhật status và gắn thuộc tính hiển thị theo thời gian thực
        $showtimes->transform(function ($showtime) use ($now) {
            $showDateStr = $showtime->show_date ? $showtime->show_date->format('Y-m-d') : today()->toDateString();
            $startDateTime = Carbon::parse($showDateStr . ' ' . $showtime->start_time);
            
            if ($showtime->end_time) {
                $endDateTime = Carbon::parse($showDateStr . ' ' . $showtime->end_time);
            } else {
                $duration = $showtime->movie ? ($showtime->movie->duration ?? 120) : 120;
                $endDateTime = $startDateTime->copy()->addMinutes($duration);
            }

            $oldStatus = $showtime->status;

            if ($now->gte($endDateTime)) {
                $realTimeStatus = 'finished'; // Đã chiếu
            } elseif ($now->gte($startDateTime)) {
                $realTimeStatus = 'showing';  // Đang chiếu
            } elseif ($showtime->status === 'upcoming' && $showtime->publish_at && $showtime->publish_at->gt($now)) {
                $realTimeStatus = 'upcoming'; // Sắp mở bán
            } else {
                $realTimeStatus = 'active';   // Mở bán / Đặt vé
            }

            // Tự động cập nhật DB nếu trạng thái thay đổi sang showing hoặc finished
            if (in_array($realTimeStatus, ['finished', 'showing']) && $oldStatus !== $realTimeStatus && $oldStatus !== 'cancelled') {
                $showtime->status = $realTimeStatus;
                $showtime->saveQuietly();
            }

            // Gắn các thông tin phụ trợ phục vụ hiển thị ở frontend customer
            $showtime->real_time_status = $realTimeStatus;
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
