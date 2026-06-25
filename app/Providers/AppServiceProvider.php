<?php

namespace App\Providers;

use App\Models\Showtime;
use App\Policies\ShowtimePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        Paginator::useTailwind();

        // Đăng ký Policy cho Showtime Model
        Gate::policy(Showtime::class, ShowtimePolicy::class);
    }
}
