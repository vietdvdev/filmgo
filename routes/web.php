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
use App\Http\Controllers\Admin\ComboItemController;
use App\Http\Controllers\Admin\ManagementAuthController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\FormatController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerForgotPasswordController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerResetPasswordController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\MovieController as CustomerMovieController;
use App\Http\Controllers\Customer\ComboShopController;
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
Route::get('/movies/{id}', [CustomerMovieController::class, 'show'])->name('movies.show');
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

// Quản lý hồ sơ cá nhân khách hàng (Yêu cầu đăng nhập với quyền Customer)
Route::middleware('customer')->group(function () {
    Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [CustomerProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('profile.password');

    // Đặt vé xem phim & chọn Combo
    Route::get('/booking/showtime/{showtime_id}/seats', [App\Http\Controllers\Customer\BookingController::class, 'selectSeats'])->name('booking.select-seats');
    Route::post('/booking/showtime/{showtime_id}/seats', [App\Http\Controllers\Customer\BookingController::class, 'processSeats'])->name('booking.process-seats');
    
    Route::get('/booking/showtime/{showtime_id}/combos', [App\Http\Controllers\Customer\BookingController::class, 'selectCombos'])->name('booking.select-combos');
    Route::post('/booking/showtime/{showtime_id}/combos', [App\Http\Controllers\Customer\BookingController::class, 'processCombos'])->name('booking.process-combos');
    // Nhả ghế khi khách bấm Quay lại từ trang Combo → về trang Chọn Ghế
    Route::post('/booking/showtime/{showtime_id}/release-seats', [App\Http\Controllers\Customer\BookingController::class, 'releaseSeats'])->name('booking.release-seats');
    // API nhả ghế qua Beacon (dùng khi user thoát trang/đóng tab — không cần redirect)
    Route::post('/booking/showtime/{showtime_id}/release-seats-beacon', [App\Http\Controllers\Customer\BookingController::class, 'releaseSeatsBeacon'])->name('booking.release-seats-beacon');

    
    Route::get('/booking/showtime/{showtime_id}/checkout', [App\Http\Controllers\Customer\BookingController::class, 'checkout'])->name('booking.checkout');
    Route::post('/booking/showtime/{showtime_id}/confirm', [App\Http\Controllers\Customer\BookingController::class, 'confirm'])->name('booking.confirm');
    Route::get('/booking/success/{booking_id}', [App\Http\Controllers\Customer\BookingController::class, 'success'])->name('booking.success');
    Route::get('/booking/payment/qr/{booking_id}/{provider}', [App\Http\Controllers\Customer\BookingController::class, 'paymentQrPage'])->name('booking.payment.qr');
    Route::get('/booking/payment/demo/{booking_id}/{provider}', [App\Http\Controllers\Customer\BookingController::class, 'demoPaymentPage'])->name('booking.payment.demo');
    Route::post('/booking/payment/demo/{booking_id}/{provider}/complete', [App\Http\Controllers\Customer\BookingController::class, 'demoPaymentComplete'])->name('booking.payment.demo.complete');

    // Voucher / Mã khuyến mãi
    Route::post('/booking/showtime/{showtime_id}/voucher/apply', [App\Http\Controllers\Customer\VoucherController::class, 'apply'])->name('booking.voucher.apply');
    Route::post('/booking/showtime/{showtime_id}/voucher/remove', [App\Http\Controllers\Customer\VoucherController::class, 'remove'])->name('booking.voucher.remove');

    // Lịch sử đặt vé
    Route::get('/booking/history', [App\Http\Controllers\Customer\BookingHistoryController::class, 'index'])->name('booking.history.index');
    Route::get('/booking/history/{id}', [App\Http\Controllers\Customer\BookingHistoryController::class, 'show'])->name('booking.history.show');

    // ── Mua Combo / F&B riêng lẻ (không cần đặt vé) ──────────────────────────────
    Route::get('/shop/combos', [ComboShopController::class, 'index'])->name('combo-shop.index');
    Route::post('/shop/combos/cart', [ComboShopController::class, 'updateCart'])->name('combo-shop.cart');
    Route::post('/shop/combos/select-cinema', [ComboShopController::class, 'selectCinema'])->name('combo-shop.select-cinema');
    Route::get('/shop/combos/checkout', [ComboShopController::class, 'checkout'])->name('combo-shop.checkout');
    Route::post('/shop/combos/confirm', [ComboShopController::class, 'confirm'])->name('combo-shop.confirm');
    Route::get('/shop/combos/success/{id}', [ComboShopController::class, 'success'])->name('combo-shop.success');
    Route::get('/shop/combos/payment/qr/{id}/{provider}', [ComboShopController::class, 'paymentQrPage'])->name('combo-shop.payment.qr');
    Route::get('/shop/combos/payment/demo/{id}/{provider}', [ComboShopController::class, 'demoPaymentPage'])->name('combo-shop.payment.demo');
    Route::post('/shop/combos/payment/demo/{id}/{provider}/complete', [ComboShopController::class, 'demoPaymentComplete'])->name('combo-shop.payment.demo.complete');
    Route::post('/shop/combos/voucher/apply', [ComboShopController::class, 'applyVoucher'])->name('combo-shop.voucher.apply');
    Route::post('/shop/combos/voucher/remove', [ComboShopController::class, 'removeVoucher'])->name('combo-shop.voucher.remove');
});

// Thanh toán callback route không yêu cầu auth để nhận redirect/IPN từ đối tác
Route::match(['get', 'post'], '/booking/vnpay-callback', [App\Http\Controllers\Customer\BookingController::class, 'vnpayCallback'])->name('booking.vnpay.callback');
Route::match(['get', 'post'], '/booking/momo-callback', [App\Http\Controllers\Customer\BookingController::class, 'momoCallback'])->name('booking.momo.callback');

// Combo Shop callbacks (không cần auth)
Route::get('/shop/combos/vnpay-callback', [ComboShopController::class, 'vnpayCallback'])->name('combo-shop.vnpay.callback');
Route::get('/shop/combos/momo-callback', [ComboShopController::class, 'momoCallback'])->name('combo-shop.momo.callback');

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
        Route::get('users/trashed', [UserController::class, 'trashed'])->name('admin.users.trashed');
        Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
        Route::resource('users', UserController::class)->names('admin.users');

        // 7. Quản lý rạp chiếu
        Route::resource('cinemas', CinemaController::class)->names('admin.cinemas');

        // 8. Quản lý phân công rạp
        Route::resource('user-cinemas', UserCinemaController::class)->names('admin.user-cinemas');

        // 9. Quản lý Combo bắp nước & Thành phần
        Route::resource('combos', ComboController::class)->names('admin.combos');
        Route::post('combo-items/{comboItem}/toggle-status', [ComboItemController::class, 'toggleStatus'])->name('admin.combo-items.toggle-status');
        Route::resource('combo-items', ComboItemController::class)->names('admin.combo-items');

        // 10. Quản lý mã khuyến mãi (Promotions)
        Route::resource('promotions', PromotionController::class)->names('admin.promotions');


        // 11. Quản lý Vé & Đơn Hàng
        Route::get('bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
        Route::get('bookings/{id}', [BookingController::class, 'show'])->name('admin.bookings.show');

        // 12. Định dạng phòng chiếu
        Route::resource('formats', FormatController::class)->names('admin.formats')->except(['create', 'edit', 'show']);

        // 13. Báo cáo doanh thu
        Route::get('reports', [AdminReportController::class, 'index'])->name('admin.reports.index');


    });
});

// Toàn bộ các đường dẫn thuộc hệ thống Manager
Route::prefix('manager')->group(function () {

    // 1. Trang đăng nhập Manager (chỉ dành cho khách chưa đăng nhập)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ManagerAuthController::class, 'showLoginForm'])->name('manager.login');
        Route::post('/login', [ManagerAuthController::class, 'login'])->name('manager.login.submit');
    });

    // 1.5. Trang thông báo "Chưa được phân công rạp" (Case 3) — chỉ cần auth, không cần middleware manager
    Route::middleware('auth')->get('/no-cinema', [ManagerAuthController::class, 'noCinema'])->name('manager.no-cinema');

    // 2. Các trang yêu cầu đăng nhập và vai trò manager
    Route::middleware(['auth', 'manager'])->group(function () {
        // Đăng xuất
        Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('manager.logout');

        Route::get('/dashboard', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'index'])->name('manager.dashboard');

        // Dashboard API endpoints
        Route::get('/dashboard/api/kpis', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'kpis'])->name('manager.dashboard.kpis');
        Route::get('/dashboard/api/charts/revenue', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'chartsRevenue'])->name('manager.dashboard.charts.revenue');
        Route::get('/dashboard/api/charts/top-movies', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'chartsTopMovies'])->name('manager.dashboard.charts.top-movies');
        Route::get('/dashboard/api/ops/today-showtimes', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'opsTodayShowtimes'])->name('manager.dashboard.ops.today-showtimes');
        Route::get('/dashboard/api/recent-bookings', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'recentBookings'])->name('manager.dashboard.recent-bookings');
        Route::get('/dashboard/api/recent-combo-bookings', [App\Http\Controllers\Manager\ManagerDashboardController::class, 'recentComboBookings'])->name('manager.dashboard.recent-combo-bookings');


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
        Route::get('/showtimes/auto-generate', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'showAutoGenerateForm'])->name('manager.showtimes.auto-generate.view');
        Route::get('/showtimes/create', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'create'])->name('manager.showtimes.create');
        Route::post('/showtimes', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'store'])->name('manager.showtimes.store');
        Route::patch('/showtimes/{id}/cancel', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'cancelShowtime'])->name('manager.showtimes.cancel');
        Route::get('/showtimes/{id}/seats', [App\Http\Controllers\Manager\ManagerShowtimeController::class, 'seatStatus'])->name('manager.showtimes.seats');

        // API endpoints cho Vue Form (paths tương đối so với prefix /manager)
        Route::get('/showtimes/api/formats-by-movie/{movieId}', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'getFormatsByMovie'])->name('manager.showtimes.api.formats-by-movie');
        Route::get('/showtimes/api/rooms-by-movie/{movieId}', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'getRoomsByMovie'])->name('manager.showtimes.api.rooms-by-movie');
        Route::get('/showtimes/api/compatible-rooms', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'getCompatibleRooms'])->name('manager.showtimes.api.compatible-rooms');
        Route::get('/showtimes/api/check-overlap', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'checkOverlap'])->name('manager.showtimes.api.check-overlap');
        Route::get('/showtimes/api/suggest-price', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'suggestPrice'])->name('manager.showtimes.api.suggest-price');
        Route::post('/showtimes/api/store', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'store'])->name('manager.showtimes.api.store');
        Route::post('/showtimes/api/auto-generate', [App\Http\Controllers\Manager\Api\AutoGenerateController::class, 'autoGenerate'])->name('manager.showtimes.api.auto-generate');

        // Thêm API endpoint cho rạp và phòng chiếu (dưới prefix /manager)
        Route::get('/api/admin/my-cinemas', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'myCinemas'])->name('manager.api.my-cinemas');
        Route::get('/api/admin/cinemas/{cinema_id}/rooms', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'roomsByCinema'])->name('manager.api.rooms-by-cinema');

        // Báo cáo & Thống kê
        Route::get('/reports', [App\Http\Controllers\Manager\ManagerReportController::class, 'index'])->name('manager.reports.index');

        // ── Quản lý tài khoản cá nhân Manager ────────────────────────────
        Route::get('/profile', [App\Http\Controllers\Manager\ManagerProfileController::class, 'edit'])->name('manager.profile.edit');
        Route::put('/profile', [App\Http\Controllers\Manager\ManagerProfileController::class, 'updateProfile'])->name('manager.profile.update');
        Route::put('/profile/password', [App\Http\Controllers\Manager\ManagerProfileController::class, 'updatePassword'])->name('manager.profile.password');
    });
});

// Định nghĩa thêm ở ngoài prefix /manager để đúng hoàn toàn URL /api/admin/... và /api/rooms/... mà đề bài yêu cầu
Route::middleware(['auth', 'manager'])->group(function () {
    Route::get('/api/admin/my-cinemas', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'myCinemas'])->name('api.admin.my-cinemas');
    Route::get('/api/admin/cinemas/{cinema_id}/rooms', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'roomsByCinema'])->name('api.admin.cinemas.rooms');

    // Room-first API endpoints
    Route::get('/api/rooms/{id}/movies', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'getMoviesByRoom'])->name('api.rooms.movies');
    Route::get('/api/rooms/{room_id}/movies/{movie_id}/formats', [App\Http\Controllers\Manager\Api\ManagerShowtimeApiController::class, 'getIntersectionFormats'])->name('api.rooms.movies.formats');
});

// ── Staff Portal ─────────────────────────────────────────────────────────────
Route::prefix('staff')->group(function () {

    // Trang đăng nhập (chỉ khách chưa đăng nhập)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Staff\StaffAuthController::class, 'showLoginForm'])->name('staff.login');
        Route::post('/login', [App\Http\Controllers\Staff\StaffAuthController::class, 'login'])->name('staff.login.submit');
    });

    // Các trang yêu cầu đăng nhập và vai trò staff
    Route::middleware(['auth', 'staff'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\Staff\StaffAuthController::class, 'logout'])->name('staff.logout');

        // Lịch chiếu hôm nay
        Route::get('/showtimes', [App\Http\Controllers\Admin\StaffShowtimeController::class, 'index'])->name('staff.showtimes.index');

        // Danh sách vé đặt trong ngày của nhân viên rạp
        Route::get('/bookings', [App\Http\Controllers\Staff\StaffBookingController::class, 'index'])->name('staff.bookings.index');

        // Quản lý đơn combo/F&B tại rạp
        Route::get('/combo-bookings', [App\Http\Controllers\Staff\StaffComboBookingController::class, 'index'])->name('staff.combo-bookings.index');
        Route::get('/combo-bookings/{bookingId}/print-receipt', [App\Http\Controllers\Staff\StaffComboBookingController::class, 'printReceipt'])->name('staff.combo-bookings.print-receipt');
        Route::post('/combo-bookings/{bookingId}/mark-printed', [App\Http\Controllers\Staff\StaffComboBookingController::class, 'markPrinted'])->name('staff.combo-bookings.mark-printed');

        // API Lấy danh sách QR Code của đơn hàng
        Route::get('/bookings/{bookingId}/qr', [App\Http\Controllers\Staff\StaffBookingController::class, 'getTicketsQR'])->name('staff.bookings.qr');

        // In vé và phiếu bắp nước qua máy in nhiệt tại quầy
        Route::get('/bookings/{bookingId}/print-tickets', [App\Http\Controllers\Staff\StaffBookingController::class, 'printTickets'])->name('staff.bookings.print-tickets');

        // ── Phân hệ POS — Bán vé tại quầy ─────────────────────────────────
        // Trang giao diện POS chính (One-page SPA)
        Route::get('/pos', [App\Http\Controllers\Staff\PosController::class, 'index'])->name('staff.pos.index');

        // API: Lấy danh sách suất chiếu theo ngày
        Route::get('/pos/api/showtimes', [App\Http\Controllers\Staff\PosController::class, 'apiGetShowtimes'])->name('staff.pos.api.showtimes');

        // API: Sơ đồ ghế real-time theo suất chiếu
        Route::get('/pos/api/seat-map/{showtime_id}', [App\Http\Controllers\Staff\PosController::class, 'apiGetSeatMap'])->name('staff.pos.api.seat-map');

        // API: Xác minh mã voucher
        Route::post('/pos/api/voucher', [App\Http\Controllers\Staff\PosController::class, 'apiCheckVoucher'])->name('staff.pos.api.voucher');

        // API: Checkout — tạo đơn + thanh toán ngay lập tức
        Route::post('/pos/api/checkout', [App\Http\Controllers\Staff\PosController::class, 'apiCheckout'])->name('staff.pos.api.checkout');

        // API: Bán F&B đơn lẻ không cần suất chiếu
        Route::get('/pos/api/combo-items', [App\Http\Controllers\Staff\PosController::class, 'apiGetComboItems'])->name('staff.pos.api.combo-items');
        Route::post('/pos/api/checkout-fnb', [App\Http\Controllers\Staff\PosController::class, 'apiCheckoutFnb'])->name('staff.pos.api.checkout-fnb');

        // ── Quản lý tài khoản cá nhân nhân viên ─────────────────────────────
        Route::get('/profile', [App\Http\Controllers\Staff\StaffProfileController::class, 'edit'])->name('staff.profile.edit');
        Route::put('/profile', [App\Http\Controllers\Staff\StaffProfileController::class, 'updateProfile'])->name('staff.profile.update');
        Route::put('/profile/password', [App\Http\Controllers\Staff\StaffProfileController::class, 'updatePassword'])->name('staff.profile.password');
    });
});

