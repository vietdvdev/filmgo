<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — FilmGo
|--------------------------------------------------------------------------
|
| Các route API trả về JSON, dùng prefix /api/*
| Tất cả request tới /api/* sẽ được render JSON tự động (xem bootstrap/app.php)
|
*/

// ── Manager API ───────────────────────────────────────────────────────────────
// Endpoint đồng bộ sơ đồ ghế — được gọi từ SeatMapBuilder.vue
// Auth guard: session cookie (auth:web) + kiểm tra quyền manager trong controller
Route::middleware(['web', 'auth', 'manager'])
    ->prefix('manager')
    ->group(function () {

        // POST /api/manager/rooms/{roomId}/seats/sync
        Route::post(
            '/rooms/{roomId}/seats/sync',
            [App\Http\Controllers\Manager\ManagerRoomController::class, 'syncSeats']
        )->name('api.manager.rooms.sync-seats');

        // GET /api/manager/rooms
        Route::get(
            '/rooms',
            [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'apiGetRooms']
        )->name('api.manager.rooms');

        // GET /api/manager/showtimes
        Route::get(
            '/showtimes',
            [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'apiGetShowtimes']
        )->name('api.manager.showtimes');

        // POST /api/manager/showtimes/bulk-open-sales
        Route::post(
            '/showtimes/bulk-open-sales',
            [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'apiBulkOpenSales']
        )->name('api.manager.showtimes.bulk-open-sales');

        // DELETE /api/manager/showtimes/{id}
        Route::delete(
            '/showtimes/{id}',
            [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'apiDeleteShowtime']
        )->name('api.manager.showtimes.delete');
    });

// ── Admin Dashboard API ───────────────────────────────────────────────────────
// Khai báo theo yêu cầu: prefix api/admin/dashboard và các endpoint tương ứng.
// Do dự án FilmGo sử dụng session-based authentication qua web cookie,
// các route này được bảo vệ bởi middleware 'web', 'auth', 'admin'.
Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/dashboard')
    ->group(function () {
        // GET /api/admin/dashboard/kpis
        Route::get('/kpis', [App\Http\Controllers\Admin\DashboardController::class, 'kpis'])->name('api.admin.dashboard.kpis');

        // GET /api/admin/dashboard/charts/revenue
        Route::get('/charts/revenue', [App\Http\Controllers\Admin\DashboardController::class, 'chartsRevenue'])->name('api.admin.dashboard.charts.revenue');

        // GET /api/admin/dashboard/charts/top-movies
        Route::get('/charts/top-movies', [App\Http\Controllers\Admin\DashboardController::class, 'chartsTopMovies'])->name('api.admin.dashboard.charts.top-movies');

        // GET /api/admin/dashboard/ops/conflicts
        Route::get('/ops/conflicts', [App\Http\Controllers\Admin\DashboardController::class, 'opsConflicts'])->name('api.admin.dashboard.ops.conflicts');

        // GET /api/admin/dashboard/ops/today-showtimes
        Route::get('/ops/today-showtimes', [App\Http\Controllers\Admin\DashboardController::class, 'opsTodayShowtimes'])->name('api.admin.dashboard.ops.today-showtimes');

        // POST /api/admin/dashboard/ops/conflicts/{id}/resolve
        Route::post('/ops/conflicts/{id}/resolve', [App\Http\Controllers\Admin\DashboardController::class, 'resolveConflict'])->name('api.admin.dashboard.ops.conflicts.resolve');
    });

