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
