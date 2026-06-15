-- =================================================================================
-- TÀI LIỆU THIẾT KẾ CƠ SỞ DỮ LIỆU HỆ THỐNG FILMGO
-- Tương thích: MySQL / MariaDB (Laravel Eloquent ORM)
-- =================================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================
-- NHÓM 1: NGƯỜI DÙNG & PHÂN QUYỀN
-- ==========================================

-- Bảng 1: Quản lý thông tin người dùng hệ thống (Khách hàng, Nhân viên, Quản lý)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính hệ thống',
    full_name VARCHAR(255) NOT NULL COMMENT 'Họ và tên người dùng',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Địa chỉ email đăng nhập',
    phone VARCHAR(20) NULL COMMENT 'Số điện thoại liên hệ',
    password VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã được mã hóa (bcrypt)',
    avatar VARCHAR(255) NULL COMMENT 'Đường dẫn ảnh đại diện',
    email_verified_at TIMESTAMP NULL COMMENT 'Thời điểm xác thực email',
    status ENUM('active', 'locked') DEFAULT 'active' COMMENT 'Trạng thái tài khoản',
    created_at TIMESTAMP NULL COMMENT 'Thời gian khởi tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm phục vụ audit'
);

-- Bảng 2: Định nghĩa các vai trò trong hệ thống
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên vai trò: admin, manager, staff, customer',
    description VARCHAR(255) NULL COMMENT 'Mô tả quyền hạn của vai trò'
);

-- Bảng 3: Bảng trung gian phân quyền người dùng (N-N)
CREATE TABLE IF NOT EXISTS user_roles (
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã người dùng',
    role_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã vai trò',
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Bảng 8: Quản lý chi nhánh rạp chiếu phim (Cần tạo trước user_cinemas)
CREATE TABLE IF NOT EXISTS cinemas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Mã chi nhánh',
    name VARCHAR(255) NOT NULL COMMENT 'Tên rạp (VD: CGV Vincom)',
    address VARCHAR(255) NOT NULL COMMENT 'Địa chỉ cụ thể',
    phone VARCHAR(20) NULL COMMENT 'Hotline rạp',
    city VARCHAR(100) NOT NULL COMMENT 'Thành phố',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Trạng thái hoạt động',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm'
);

-- Bảng 4: Phân công nhân viên/quản lý vào làm việc tại các chi nhánh rạp cụ thể
CREATE TABLE IF NOT EXISTS user_cinemas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã quản lý/nhân viên',
    cinema_id BIGINT UNSIGNED NOT NULL COMMENT 'Rạp phim được phân quyền',
    created_at TIMESTAMP NULL COMMENT 'Thời gian phân công',
    UNIQUE KEY (user_id, cinema_id) COMMENT 'Chống phân công trùng nhân viên - rạp',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cinema_id) REFERENCES cinemas(id) ON DELETE CASCADE
);

-- ==========================================
-- NHÓM 2: PHIM, THỂ LOẠI & DIỄN VIÊN
-- ==========================================

-- Bảng 5: Thông tin chi tiết của bộ phim
CREATE TABLE IF NOT EXISTS movies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Mã phim',
    title VARCHAR(255) NOT NULL COMMENT 'Tên bộ phim',
    slug VARCHAR(255) NOT NULL UNIQUE COMMENT 'Đường dẫn SEO thân thiện',
    poster VARCHAR(255) NULL COMMENT 'Ảnh poster chính',
    trailer_url VARCHAR(255) NULL COMMENT 'Liên kết trailer YouTube/Vimeo',
    duration INT NOT NULL COMMENT 'Thời lượng phim (phút)',
    release_date DATE NOT NULL COMMENT 'Ngày khởi chiếu chính thức',
    director VARCHAR(255) NULL COMMENT 'Tên đạo diễn',
    country VARCHAR(100) NULL COMMENT 'Quốc gia sản xuất',
    age_limit ENUM('P','K','T13','T16','T18') NOT NULL COMMENT 'Phân loại độ tuổi',
    description TEXT NULL COMMENT 'Mô tả nội dung phim',
    status ENUM('upcoming','showing','stopped') DEFAULT 'upcoming' COMMENT 'Trạng thái phim',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm - phục hồi được',
    FULLTEXT INDEX (title, description) COMMENT 'Tìm kiếm phim theo từ khóa'
);

-- Bảng 6: Danh mục thể loại phim
CREATE TABLE IF NOT EXISTS genres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Tên thể loại (Hành động, Kinh dị...)',
    description TEXT NULL COMMENT 'Mô tả thể loại'
);

-- Bảng 7: Quan hệ Phim và Thể loại (N-N)
CREATE TABLE IF NOT EXISTS movie_genres (
    movie_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã phim',
    genre_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã thể loại',
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

-- Bảng 26: Thông tin diễn viên (Chuẩn hóa)
CREATE TABLE IF NOT EXISTS actors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    name VARCHAR(255) NOT NULL COMMENT 'Tên diễn viên',
    avatar VARCHAR(255) NULL COMMENT 'Ảnh đại diện diễn viên',
    biography TEXT NULL COMMENT 'Tiểu sử, sự nghiệp',
    date_of_birth DATE NULL COMMENT 'Ngày sinh'
);

-- Bảng 27: Quan hệ Phim và Diễn viên
CREATE TABLE IF NOT EXISTS movie_actors (
    movie_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã phim',
    actor_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã diễn viên',
    role_name VARCHAR(100) NULL COMMENT 'Tên nhân vật đảm nhận trong phim',
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
);

-- ==========================================
-- NHÓM 3: HẠ TẦNG RẠP CHIẾU & GHẾ NGỒI
-- ==========================================

-- Bảng 9: Quản lý phòng chiếu thuộc rạp
CREATE TABLE IF NOT EXISTS rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    cinema_id BIGINT UNSIGNED NOT NULL COMMENT 'Thuộc rạp nào',
    room_name VARCHAR(100) NOT NULL COMMENT 'Tên phòng (VD: Phòng 1, Hall A)',
    capacity INT NOT NULL COMMENT 'Tổng sức chứa (số ghế)',
    room_type ENUM('2D','3D','IMAX','4DX') NOT NULL COMMENT 'Loại phòng chiếu',
    status ENUM('active','maintenance','inactive') DEFAULT 'active' COMMENT 'Trạng thái phòng',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm',
    FOREIGN KEY (cinema_id) REFERENCES cinemas(id) ON DELETE CASCADE
);

-- Bảng 10: Phân loại ghế (Cơ sở tính giá động)
CREATE TABLE IF NOT EXISTS seat_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên loại ghế: Thường, VIP, Sweetbox',
    surcharge_price INT DEFAULT 0 COMMENT 'Giá phụ thu cộng thêm so với base_price (VNĐ)'
);

-- Bảng 11: Ghế vật lý cố định trong từng phòng chiếu
CREATE TABLE IF NOT EXISTS seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    room_id BIGINT UNSIGNED NOT NULL COMMENT 'Thuộc phòng chiếu nào',
    seat_type_id BIGINT UNSIGNED NOT NULL COMMENT 'Loại ghế của vị trí này',
    seat_row VARCHAR(10) NOT NULL COMMENT 'Ký hiệu hàng ghế (A, B, C...)',
    seat_number INT NOT NULL COMMENT 'Số thứ tự ghế trên hàng (1, 2, 3...)',
    status ENUM('active','maintenance') DEFAULT 'active' COMMENT 'Trạng thái vật lý của ghế',
    UNIQUE KEY (room_id, seat_row, seat_number) COMMENT 'Chống trùng vị trí ghế trong phòng',
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_type_id) REFERENCES seat_types(id) ON DELETE RESTRICT
);

-- ==========================================
-- NHÓM 4: VẬN HÀNH SUẤT CHIẾU & GIÁ VÉ
-- ==========================================

-- Bảng 12: Quản lý lịch chiếu/suất chiếu cụ thể
CREATE TABLE IF NOT EXISTS showtimes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    movie_id BIGINT UNSIGNED NOT NULL COMMENT 'Phim được chiếu',
    room_id BIGINT UNSIGNED NOT NULL COMMENT 'Phòng chiếu',
    show_date DATE NOT NULL COMMENT 'Ngày chiếu cụ thể',
    start_time TIME NOT NULL COMMENT 'Giờ bắt đầu chiếu',
    end_time TIME NOT NULL COMMENT 'Giờ kết thúc',
    base_price INT NOT NULL COMMENT 'Giá vé cơ sở của suất (VNĐ)',
    status ENUM('upcoming','showing','finished') DEFAULT 'upcoming' COMMENT 'Trạng thái suất chiếu',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm - hủy suất chiếu',
    INDEX (movie_id, show_date) COMMENT 'Query lịch chiếu theo phim và ngày',
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Bảng 13: Trạng thái từng ghế trong 1 suất chiếu (Chống Race Condition)
CREATE TABLE IF NOT EXISTS showtime_seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    showtime_id BIGINT UNSIGNED NOT NULL COMMENT 'Thuộc suất chiếu nào',
    seat_id BIGINT UNSIGNED NOT NULL COMMENT 'Vị trí ghế vật lý tương ứng',
    user_id BIGINT UNSIGNED NULL COMMENT 'Khách đang giữ hoặc đặt ghế',
    status ENUM('available','holding','booked') DEFAULT 'available' COMMENT 'Trạng thái ghế thực tế',
    locked_at TIMESTAMP NULL COMMENT 'Thời điểm bắt đầu giữ ghế (holding)',
    expires_at TIMESTAMP NULL COMMENT 'Hạn giữ ghế - tự động nhả khi quá hạn',
    UNIQUE KEY (showtime_id, seat_id) COMMENT 'Ràng buộc chống race condition - bắt buộc',
    INDEX (status, expires_at) COMMENT 'Cron job tự động nhả ghế hết hạn',
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng 14: Cấu hình quy tắc điều chỉnh giá theo khung giờ / ngày trong tuần
CREATE TABLE IF NOT EXISTS price_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    name VARCHAR(100) NOT NULL COMMENT 'Tên quy tắc giá',
    day_of_week TINYINT NOT NULL COMMENT '0: CN, 1-6: T2-T7',
    start_time TIME NOT NULL COMMENT 'Giờ bắt đầu áp dụng khung giá',
    end_time TIME NOT NULL COMMENT 'Giờ kết thúc khung giá',
    adjustment_amount INT DEFAULT 0 COMMENT 'Số tiền cộng/trừ vào base_price (VNĐ)',
    is_active TINYINT DEFAULT 1 COMMENT 'Quy tắc đang được áp dụng (1=Có, 0=Không)',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật'
);

-- Bảng 30: Cấu hình ngày lễ để áp dụng giá đặc biệt
CREATE TABLE IF NOT EXISTS holidays (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    name VARCHAR(100) NOT NULL COMMENT 'Tên ngày lễ (VD: Tet Nguyen Dan, Quoc Khanh)',
    holiday_date DATE NOT NULL UNIQUE COMMENT 'Tra cứu ngày lễ không trùng lặp',
    description VARCHAR(255) NULL COMMENT 'Ghi chú thêm về ngày lễ'
);

-- ==========================================
-- NHÓM 5: ĐẶT VÉ & THANH TOÁN
-- ==========================================

-- Bảng 15: Đơn đặt vé tổng hợp (Booking)
CREATE TABLE IF NOT EXISTS bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Khách hàng đặt vé',
    showtime_id BIGINT UNSIGNED NOT NULL COMMENT 'Suất chiếu tương ứng',
    booking_code VARCHAR(100) NOT NULL UNIQUE COMMENT 'Mã tra cứu đơn (VD: FLM-9283)',
    total_amount INT NOT NULL COMMENT 'Tổng tiền sau cùng cần thanh toán',
    discount_amount INT DEFAULT 0 COMMENT 'Số tiền đã được giảm (lưu vết đối soát)',
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending' COMMENT 'Trạng thái thanh toán',
    booking_status ENUM('pending','confirmed','cancelled') DEFAULT 'pending' COMMENT 'Trạng thái xác nhận vé',
    expired_at TIMESTAMP NOT NULL COMMENT 'Hạn chót thanh toán - quá hạn hủy tự động',
    created_at TIMESTAMP NULL COMMENT 'Thời điểm lên đơn',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật cuối',
    INDEX (user_id, created_at) COMMENT 'Lịch sử đặt vé của user',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE
);

-- Bảng 16: Chi tiết các ghế được chọn trong một đơn hàng (Price Snapshot)
CREATE TABLE IF NOT EXISTS booking_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    booking_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã đơn đặt vé',
    showtime_seat_id BIGINT UNSIGNED NOT NULL COMMENT 'Tham chiếu trạng thái ghế của suất chiếu',
    price INT NOT NULL COMMENT 'Giá chốt tại thời điểm mua - Price Snapshot',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (showtime_seat_id) REFERENCES showtime_seats(id) ON DELETE CASCADE
);

-- Bảng 17: Lưu vết lịch sử giao dịch thanh toán
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    booking_id BIGINT UNSIGNED NOT NULL COMMENT 'Đơn đặt vé được thanh toán',
    transaction_code VARCHAR(100) NULL COMMENT 'Mã giao dịch từ cổng thanh toán',
    amount INT NOT NULL COMMENT 'Số tiền giao dịch (VNĐ)',
    payment_method VARCHAR(50) NOT NULL COMMENT 'Phương thức: Credit, Momo, VNPay, ZaloPay',
    payment_status ENUM('pending','success','failed','refunded') NOT NULL COMMENT 'Trạng thái cổng báo về',
    paid_at TIMESTAMP NULL COMMENT 'Thời điểm thanh toán thành công',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ==========================================
-- NHÓM 6: COMBO ĐỒ ĂN / BẮP NƯỚC
-- ==========================================

-- Bảng 18: Danh mục Combo / Đồ ăn
CREATE TABLE IF NOT EXISTS combos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    combo_name VARCHAR(255) NOT NULL COMMENT 'Tên combo',
    image VARCHAR(255) NULL COMMENT 'Hình ảnh minh họa combo',
    price INT NOT NULL COMMENT 'Giá bán (VNĐ)',
    description TEXT NULL COMMENT 'Thành phần chi tiết combo',
    status ENUM('active','inactive') DEFAULT 'active' COMMENT 'Trạng thái kinh doanh',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm'
);

-- Bảng 19: Chi tiết mua bắp nước trong đơn hàng (Price Snapshot)
CREATE TABLE IF NOT EXISTS booking_combos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    booking_id BIGINT UNSIGNED NOT NULL COMMENT 'Đơn đặt vé tương ứng',
    combo_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã combo đã chọn',
    quantity INT NOT NULL COMMENT 'Số lượng đặt mua',
    subtotal INT NOT NULL COMMENT 'Thành tiền - Snapshot',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE RESTRICT
);

-- ==========================================
-- NHÓM 7: KHUYẾN MÃI
-- ==========================================

-- Bảng 20: Quản lý mã giảm giá / khuyến mãi
CREATE TABLE IF NOT EXISTS promotions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã nhập của khách (VD: SALE50)',
    discount_type ENUM('percent','fixed') NOT NULL COMMENT 'Loại giảm: % hoặc tiền',
    discount_value INT NOT NULL COMMENT 'Giá trị giảm',
    min_order_amount INT DEFAULT 0 COMMENT 'Giá trị đơn tối thiểu để áp dụng mã',
    max_uses_per_user INT DEFAULT 1 COMMENT 'Số lần tối đa một user được dùng',
    start_date TIMESTAMP NOT NULL COMMENT 'Ngày bắt đầu áp dụng',
    end_date TIMESTAMP NOT NULL COMMENT 'Ngày kết thúc áp dụng',
    quantity INT NULL COMMENT 'Tổng lượt sử dụng (Null = vô hạn)',
    status ENUM('active','inactive') DEFAULT 'active' COMMENT 'Trạng thái mã',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm'
);

-- Bảng 21: Lưu vết áp dụng mã giảm giá cho đơn hàng
CREATE TABLE IF NOT EXISTS booking_promotions (
    booking_id BIGINT UNSIGNED NOT NULL COMMENT 'Đơn hàng được áp mã',
    promotion_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã khuyến mãi được sử dụng',
    PRIMARY KEY (booking_id, promotion_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE
);

-- ==========================================
-- NHÓM 8: ĐÁNH GIÁ, VÉ, THÔNG BÁO & AUDIT
-- ==========================================

-- Bảng 22: Đánh giá phim từ người dùng
CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Khách hàng viết đánh giá',
    movie_id BIGINT UNSIGNED NOT NULL COMMENT 'Phim được đánh giá',
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5) COMMENT 'Điểm số từ 1 đến 5 sao',
    comment TEXT NULL COMMENT 'Nội dung bình luận',
    status ENUM('pending','approved','rejected') DEFAULT 'approved' COMMENT 'Trạng thái kiểm duyệt nội dung',
    created_at TIMESTAMP NULL COMMENT 'Thời gian viết đánh giá',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm nếu vi phạm nội dung',
    UNIQUE KEY (user_id, movie_id) COMMENT 'Mỗi user chỉ đánh giá 1 phim 1 lần',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
);

-- Bảng 23: Vé điện tử sinh ra sau khi thanh toán thành công
CREATE TABLE IF NOT EXISTS tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    booking_detail_id BIGINT UNSIGNED NOT NULL COMMENT 'Ghế tương ứng trên chi tiết đơn',
    qr_code VARCHAR(255) NOT NULL UNIQUE COMMENT 'Chuỗi định danh để mã hóa QR',
    ticket_status ENUM('unused','used','cancelled') DEFAULT 'unused' COMMENT 'Trạng thái vé',
    checked_in_at TIMESTAMP NULL COMMENT 'Thời gian quét mã vào rạp',
    INDEX (qr_code) COMMENT 'Check-in QR phải lookup cực nhanh',
    FOREIGN KEY (booking_detail_id) REFERENCES booking_details(id) ON DELETE CASCADE
);

-- Bảng 24: Thông báo gửi tới người dùng
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Người nhận thông báo',
    type VARCHAR(50) NOT NULL COMMENT 'Loại: booking_confirmed, check_in_reminder...',
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề thông báo',
    content TEXT NOT NULL COMMENT 'Nội dung chi tiết',
    is_read TINYINT DEFAULT 0 COMMENT 'Đã đọc chưa (0: Chưa, 1: Rồi)',
    created_at TIMESTAMP NULL COMMENT 'Thời điểm gửi',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng 25: Nhật ký thao tác - Audit Trail hệ thống
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Tài khoản thực hiện thao tác',
    action VARCHAR(100) NOT NULL COMMENT 'Tên hành động',
    model_type VARCHAR(100) NULL COMMENT 'Loại đối tượng bị tác động',
    model_id BIGINT NULL COMMENT 'ID đối tượng bị tác động',
    description TEXT NULL COMMENT 'Mô tả chi tiết hành động',
    ip_address VARCHAR(45) NULL COMMENT 'Địa chỉ IP khi thực hiện',
    created_at TIMESTAMP NULL COMMENT 'Thời gian ghi log',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- NHÓM 9: BÀI VIẾT & BẢO MẬT
-- ==========================================

-- Bảng 28: Quản lý bài viết Tin tức / Sự kiện
CREATE TABLE IF NOT EXISTS posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Khóa chính',
    author_id BIGINT UNSIGNED NOT NULL COMMENT 'Admin/Staff viết bài',
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề bài viết',
    slug VARCHAR(255) NOT NULL UNIQUE COMMENT 'Đường dẫn SEO',
    thumbnail VARCHAR(255) NULL COMMENT 'Ảnh bìa bài viết',
    content LONGTEXT NOT NULL COMMENT 'Nội dung đầy đủ (HTML)',
    status ENUM('draft','published','archived') DEFAULT 'draft' COMMENT 'Trạng thái xuất bản',
    published_at TIMESTAMP NULL COMMENT 'Thời gian xuất bản',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP NULL COMMENT 'Thời gian cập nhật',
    deleted_at TIMESTAMP NULL COMMENT 'Xóa mềm',
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng 29: Token đặt lại mật khẩu (Quên mật khẩu)
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY COMMENT 'Email yêu cầu',
    token VARCHAR(255) NOT NULL COMMENT 'Chuỗi token bí mật',
    created_at TIMESTAMP NULL COMMENT 'Thời gian tạo token'
);

SET FOREIGN_KEY_CHECKS = 1;