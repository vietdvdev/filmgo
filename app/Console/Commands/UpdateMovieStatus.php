<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('movies:update-status')]
#[Description('Tự động chuyển trạng thái phim upcoming sang showing khi đến ngày khởi chiếu')]
class UpdateMovieStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $updatedCount = Movie::where('status', 'upcoming')
            ->whereNotNull('release_date')
            ->whereDate('release_date', '<=', $now)
            ->update(['status' => 'showing']);

        if ($updatedCount > 0) {
            $message = "Đã tự động cập nhật trạng thái {$updatedCount} phim từ upcoming sang showing vào lúc {$now->toDateTimeString()}";
            $this->info($message);
            \Illuminate\Support\Facades\Log::info($message);
        }
    }
}
