<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\UserCinemaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerForgotPasswordController;
use App\Http\Controllers\Customer\CustomerResetPasswordController;
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
    Route::get('/profile', [\App\Http\Controllers\Customer\CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Customer\CustomerProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Customer\CustomerProfileController::class, 'updatePassword'])->name('profile.password');
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

    // 6. Quản lý người dùng
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::resource('users', UserController::class)->names('admin.users');

    // 6. Quản lý phân công rạp
    Route::resource('user-cinemas', UserCinemaController::class)
    ->names('admin.user-cinemas');
});
