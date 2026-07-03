<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('showtimes:update-status')]
#[Description('Tự động kiểm tra và chuyển trạng thái suất chiếu sang active nếu đến thời điểm publish_at')]
class UpdateShowtimeStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $updatedCount = \App\Models\Showtime::where('status', 'upcoming')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', $now)
            ->update(['status' => 'active']);

        if ($updatedCount > 0) {
            $this->info("Đã tự động mở bán {$updatedCount} suất chiếu vào lúc {$now->toDateTimeString()}.");
            \Illuminate\Support\Facades\Log::info("Auto-publish showtimes: {$updatedCount} suất chiếu đã được mở bán tự động.");
        }
    }
}
