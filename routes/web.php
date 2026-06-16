<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MovieController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Mặc định chạy link gốc sẽ đá về trang login của admin luôn cho tiện làm việc
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Toàn bộ các đường dẫn thuộc hệ thống Admin
Route::prefix('admin')->group(function () {

    // 1. Nhóm các đường dẫn xác thực (Đăng nhập)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);

    // 2. Đường dẫn xử lý Đăng xuất (Bắt buộc dùng POST để bảo mật)
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // 3. Đường dẫn vào trang quản trị Dashboard chính
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 4. Quản lý thể loại phim
    Route::resource('genres', GenreController::class)->names('admin.genres');

    // 5. Quản lý phim
    Route::post('movies/{id}/restore', [MovieController::class, 'restore'])->name('admin.movies.restore');
    Route::resource('movies', MovieController::class)->names('admin.movies');
});
