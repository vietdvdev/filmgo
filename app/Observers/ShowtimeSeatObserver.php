<?php

namespace App\Observers;

use App\Models\ShowtimeSeat;

class ShowtimeSeatObserver
{
    /**
     * Handle the ShowtimeSeat "updating" event.
     */
    public function updating(ShowtimeSeat $showtimeSeat): void
    {
        // Logic tự động hủy gán nhân viên nếu ghế đôi bị hủy vé (đưa về available)
        // hoặc đưa vào bảo trì (maintenance)
        if ($showtimeSeat->isDirty('status')) {
            $newStatus = $showtimeSeat->status;
            
            // Nếu ghế chuyển về trạng thái available (trống) hoặc bảo trì, ta hủy gán nhân viên (reset employee_id)
            if (in_array($newStatus, ['available', 'maintenance'])) {
                $showtimeSeat->employee_id = null;
            }
        }
    }

    /**
     * Handle the ShowtimeSeat "created" event.
     */
    public function created(ShowtimeSeat $showtimeSeat): void
    {
        //
    }

    /**
     * Handle the ShowtimeSeat "updated" event.
     */
    public function updated(ShowtimeSeat $showtimeSeat): void
    {
        //
    }

    /**
     * Handle the ShowtimeSeat "deleted" event.
     */
    public function deleted(ShowtimeSeat $showtimeSeat): void
    {
        //
    }

    /**
     * Handle the ShowtimeSeat "restored" event.
     */
    public function restored(ShowtimeSeat $showtimeSeat): void
    {
        //
    }

    /**
     * Handle the ShowtimeSeat "force deleted" event.
     */
    public function forceDeleted(ShowtimeSeat $showtimeSeat): void
    {
        //
    }
}
