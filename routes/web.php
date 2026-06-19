<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\UserCinemaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ManagementAuthController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerForgotPasswordController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerResetPasswordController;
use App\Http\Controllers\Manager\ManagerAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Trang chủ hệ thống khách hàng
Route::get('/', function () {
    return view('home');
})->name('home');

// Xác thực khách hàng (Khách chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register']);

    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login']);

    // Khôi phục mật khẩu
    Route::get('/forgot-password', [CustomerForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [CustomerForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [CustomerResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [CustomerResetPasswordController::class, 'reset'])->name('password.update');
});

// Đăng xuất khách hàng (Yêu cầu đăng nhập)
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout')->middleware('auth');

// Quản lý hồ sơ cá nhân khách hàng (Yêu cầu đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [CustomerProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('profile.password');
});

// Toàn bộ các đường dẫn thuộc hệ thống Admin
Route::prefix('admin')->group(function () {

    // Khách chưa đăng nhập vào admin portal
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ManagementAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [ManagementAuthController::class, 'login']);
    });

    // Nhân sự quản trị đã đăng nhập (Admin, Manager, Staff)
    Route::middleware(['auth', 'admin'])->group(function () {
        // 2. Đường dẫn xử lý Đăng xuất (Bắt buộc dùng POST để bảo mật)
        Route::post('/logout', [ManagementAuthController::class, 'logout'])->name('admin.logout');

        // 3. Đường dẫn vào trang quản trị Dashboard chính
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // 4. Quản lý thể loại phim
        Route::resource('genres', GenreController::class)->names('admin.genres');

        // 5. Quản lý phim
        Route::post('movies/{id}/restore', [MovieController::class, 'restore'])->name('admin.movies.restore');
        Route::resource('movies', MovieController::class)->names('admin.movies');

        // 6. Quản lý người dùng
        Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
        Route::resource('users', UserController::class)->names('admin.users');

        // 7. Quản lý phân công rạp
        Route::resource('user-cinemas', UserCinemaController::class)->names('admin.user-cinemas');
    });
});

// Toàn bộ các đường dẫn thuộc hệ thống Manager
Route::prefix('manager')->group(function () {

    // 1. Trang đăng nhập Manager (chỉ dành cho khách chưa đăng nhập)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ManagerAuthController::class, 'showLoginForm'])->name('manager.login');
        Route::post('/login', [ManagerAuthController::class, 'login'])->name('manager.login.submit');
    });

    // 2. Các trang yêu cầu đăng nhập và vai trò manager
    Route::middleware(['auth', 'manager'])->group(function () {
        // Đăng xuất
        Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('manager.logout');

        Route::get('/dashboard', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'index'])->name('manager.dashboard');

        // Quản lý nhân sự
        Route::get('/staff', [App\Http\Controllers\Manager\ManagerStaffController::class, 'index'])->name('manager.staff.index');
        Route::post('/staff', [App\Http\Controllers\Manager\ManagerStaffController::class, 'store'])->name('manager.staff.store');
        Route::patch('/staff/{id}/toggle', [App\Http\Controllers\Manager\ManagerStaffController::class, 'toggleStatus'])->name('manager.staff.toggle');
        Route::put('/staff/{id}/reset-password', [App\Http\Controllers\Manager\ManagerStaffController::class, 'resetPassword'])->name('manager.staff.reset-password');

        // Quản lý phòng chiếu
        Route::get('/rooms', [App\Http\Controllers\Manager\ManagerRoomController::class, 'index'])->name('manager.rooms.index');
        Route::post('/rooms', [App\Http\Controllers\Manager\ManagerRoomController::class, 'store'])->name('manager.rooms.store');
        Route::put('/rooms/{id}', [App\Http\Controllers\Manager\ManagerRoomController::class, 'update'])->name('manager.rooms.update');
        Route::delete('/rooms/{id}', [App\Http\Controllers\Manager\ManagerRoomController::class, 'destroy'])->name('manager.rooms.destroy');
        Route::get('/rooms/{roomId}/seat-map', [App\Http\Controllers\Manager\ManagerRoomController::class, 'seatMap'])->name('manager.rooms.seat-map');

        // Suất chiếu
        Route::get('/showtimes', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'index'])->name('manager.showtimes.index');
        Route::get('/showtimes/create', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'create'])->name('manager.showtimes.create');
        Route::post('/showtimes', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'store'])->name('manager.showtimes.store');
        Route::patch('/showtimes/{id}/cancel', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'cancelShowtime'])->name('manager.showtimes.cancel');

        // Báo cáo & Thống kê
        Route::get('/reports', [App\Http\Controllers\Manager\ManagerReportController::class, 'index'])->name('manager.reports.index');
    });
});
