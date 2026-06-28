<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\PriceRuleController;
use App\Http\Controllers\Admin\SeatTypeController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\UserCinemaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ComboController;
use App\Http\Controllers\Admin\ManagementAuthController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerCinemaController;
use App\Http\Controllers\Customer\CustomerForgotPasswordController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerResetPasswordController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\MovieController as CustomerMovieController;
use App\Http\Controllers\Manager\ManagerAuthController;
use App\Http\Controllers\Manager\ManagerCinemaController;
use App\Http\Controllers\Manager\ManagerStaffController;
use App\Http\Controllers\Manager\ManagerSeatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Trang chủ hệ thống khách hàng
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/movies/showing',[CustomerMovieController::class, 'showing'])->name('movies.showing');
Route::get('/movies/upcoming',[CustomerMovieController::class, 'upcoming'])->name('movies.upcoming');
Route::get(
    '/movies/search',
    [CustomerMovieController::class, 'search']
)->name('movies.search');
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

        // 4.5. Quản lý loại ghế
        Route::resource('seat-types', SeatTypeController::class)->names('admin.seat-types');

        // 4.6. Quản lý quy tắc giá phụ thu
        Route::resource('price-rules', PriceRuleController::class)->names('admin.price-rules');
        Route::post('price-rules/{priceRule}/toggle-status', [PriceRuleController::class, 'toggleStatus'])->name('admin.price-rules.toggle-status');

        // 5. Quản lý phim
        Route::post('movies/{id}/restore', [MovieController::class, 'restore'])->name('admin.movies.restore');
        Route::resource('movies', MovieController::class)->names('admin.movies');

        // 6. Quản lý người dùng
        Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
        Route::resource('users', UserController::class)->names('admin.users');

        // 7. Quản lý rạp chiếu
        Route::resource('cinemas', CinemaController::class)->names('admin.cinemas');

        // 8. Quản lý phân công rạp
        Route::resource('user-cinemas', UserCinemaController::class)->names('admin.user-cinemas');

        // 9. Quản lý Combo bắp nước
        Route::resource('combos', ComboController::class)->names('admin.combos');
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


        // Quản lý rạp chiếu (xem thông tin rạp được phân công)
        Route::get('/cinemas', [ManagerCinemaController::class, 'index'])->name('manager.cinemas.index');

        // Quản lý nhân sự
        // Staff
        Route::get('/staff', [ManagerStaffController::class, 'index'])->name('manager.staff.index');
        Route::get(
            '/staff/create',
            [ManagerStaffController::class, 'create']
        )->name('manager.staff.create');
        Route::post('/staff', [ManagerStaffController::class, 'store'])->name('manager.staff.store');
        Route::get(
            '/staff/{id}/edit',
            [ManagerStaffController::class, 'edit']
        )->name('manager.staff.edit');
        Route::put('/staff/{id}', [ManagerStaffController::class, 'update'])->name('manager.staff.update');
        Route::delete('/staff/{id}', [ManagerStaffController::class, 'destroy'])->name('manager.staff.destroy');
        Route::patch('/staff/{id}/toggle', [ManagerStaffController::class, 'toggleStatus'])->name('manager.staff.toggle');
        Route::put('/staff/{id}/reset-password', [ManagerStaffController::class, 'resetPassword'])->name('manager.staff.reset-password');

        // Quản lý phòng chiếu
        Route::get('/rooms', [App\Http\Controllers\Manager\ManagerRoomController::class, 'index'])->name('manager.rooms.index');
        Route::get('/rooms/create', [App\Http\Controllers\Manager\ManagerRoomController::class, 'create'])->name('manager.rooms.create');
        Route::post('/rooms', [App\Http\Controllers\Manager\ManagerRoomController::class, 'store'])->name('manager.rooms.store');
        Route::get('/rooms/{id}/edit', [App\Http\Controllers\Manager\ManagerRoomController::class, 'edit'])->name('manager.rooms.edit');
        Route::put('/rooms/{id}', [App\Http\Controllers\Manager\ManagerRoomController::class, 'update'])->name('manager.rooms.update');
        Route::delete('/rooms/{id}', [App\Http\Controllers\Manager\ManagerRoomController::class, 'destroy'])->name('manager.rooms.destroy');
        Route::get('/rooms/{roomId}/seat-map', [App\Http\Controllers\Manager\ManagerRoomController::class, 'seatMap'])->name('manager.rooms.seat-map');

        // Quản lý sơ đồ ghế (Thiết lập sơ đồ ghế - CRUD đơn lẻ)
        Route::get('/rooms/{roomId}/seats', [ManagerSeatController::class, 'index'])->name('manager.rooms.seats.index');
        Route::post('/rooms/{roomId}/seats/bulk', [ManagerSeatController::class, 'bulkStore'])->name('manager.rooms.seats.bulk');
        Route::put('/rooms/{roomId}/seats/{seatId}', [ManagerSeatController::class, 'update'])->name('manager.rooms.seats.update');
        Route::delete('/rooms/{roomId}/seats/{seatId}', [ManagerSeatController::class, 'destroy'])->name('manager.rooms.seats.destroy');

        // Đồng bộ toàn bộ sơ đồ ghế (Full Sync — xóa cũ, chèn mới, cập nhật capacity)
        Route::post(
            '/rooms/{roomId}/sync-seats',
            [App\Http\Controllers\Manager\ManagerRoomController::class, 'syncSeats']
        )->name('manager.rooms.sync-seats');

        // Suất chiếu
        Route::get('/showtimes', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'index'])->name('manager.showtimes.index');
        Route::get('/showtimes/create', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'create'])->name('manager.showtimes.create');
        Route::post('/showtimes', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'store'])->name('manager.showtimes.store');
        Route::patch('/showtimes/{id}/cancel', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'cancelShowtime'])->name('manager.showtimes.cancel');
        Route::get('/showtimes/{id}/seats', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'seatStatus'])->name('manager.showtimes.seats');

        // API endpoints cho Vue Form (paths tương đối so với prefix /manager)
        Route::get('/showtimes/api/check-overlap', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'checkOverlap'])->name('manager.showtimes.api.check-overlap');
        Route::get('/showtimes/api/suggest-price', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'suggestPrice'])->name('manager.showtimes.api.suggest-price');
        Route::post('/showtimes/api/store', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'store'])->name('manager.showtimes.api.store');

        // Thêm API endpoint cho rạp và phòng chiếu (dưới prefix /manager)
        Route::get('/api/admin/my-cinemas', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'myCinemas'])->name('manager.api.my-cinemas');
        Route::get('/api/admin/cinemas/{cinema_id}/rooms', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'roomsByCinema'])->name('manager.api.rooms-by-cinema');

        // Báo cáo & Thống kê
        Route::get('/reports', [App\Http\Controllers\Manager\ManagerReportController::class, 'index'])->name('manager.reports.index');
    });
});

// Định nghĩa thêm ở ngoài prefix /manager để đúng hoàn toàn URL /api/admin/... mà đề bài yêu cầu
Route::middleware(['auth', 'manager'])->group(function () {
    Route::get('/api/admin/my-cinemas', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'myCinemas'])->name('api.admin.my-cinemas');
    Route::get('/api/admin/cinemas/{cinema_id}/rooms', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'roomsByCinema'])->name('api.admin.cinemas.rooms');
});

