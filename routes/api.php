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
Route::middleware(['auth', 'manager'])
    ->prefix('manager')
    ->group(function () {

        // POST /api/manager/rooms/{roomId}/seats/sync
        Route::post(
            '/rooms/{roomId}/seats/sync',
            [App\Http\Controllers\Manager\ManagerRoomController::class, 'syncSeats']
        )->name('api.manager.rooms.sync-seats');
    });
