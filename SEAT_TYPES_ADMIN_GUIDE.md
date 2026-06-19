# 🎬 FilmGo - Hệ Thống Quản Lý Loại Ghế & Quyền Admin

## 📋 Tổng Quan

Hệ thống FilmGo đã được cấu hình với:
- **Quản lý loại ghế (Seat Types)** - Quản lý các loại ghế trong rạp (Thường, VIP, Sweetbox, etc.)
- **Hệ thống quyền Admin** - Phân quyền người dùng thành 4 vai trò: Admin, Manager, Staff, Customer

---

## 🔐 Hệ Thống Quyền & Vai Trò

### Các Vai Trò Hiện Có

| Vai Trò | Quyền | Mô Tả |
|---------|-------|-------|
| **Admin** | Toàn quyền | Quản trị viên toàn quyền hệ thống, quản lý tất cả tính năng |
| **Manager** | Quản lý rạp | Quản lý chi nhánh rạp phim, suất chiếu, nhân viên |
| **Staff** | Bán vé | Nhân viên bán vé và vận hành tại rạp |
| **Customer** | Mua vé | Khách hàng mua vé xem phim |

### Tài Khoản Mặc Định

```
Email: admin@gmail.com
Password: 123456
Role: Admin
```

---

## 🪑 Quản Lý Loại Ghế

### Truy Cập

1. Đăng nhập vào Admin Portal: `http://localhost/admin/login`
2. Sử dụng tài khoản admin@gmail.com / 123456
3. Từ menu Sidebar, click vào **"Quản Lý Loại Ghế"** (icon: event_seat)

### Chức Năng

#### 1. **Danh Sách Loại Ghế** (Index)
- Xem tất cả loại ghế trong hệ thống
- Tìm kiếm loại ghế theo tên
- Xem số lượng ghế đang sử dụng mỗi loại
- Xem giá phụ thu tương ứng

#### 2. **Thêm Loại Ghế Mới** (Create)
- Click nút "Thêm Loại Ghế"
- Nhập thông tin:
  - **Tên Loại Ghế** (bắt buộc, max 50 ký tự)
    - Ví dụ: Thường, VIP, Sweetbox, Ghế đôi
  - **Giá Phụ Thu** (bắt buộc, số nguyên >= 0)
    - Ví dụ: 0 (không phụ thu), 20000 (VIP), 40000 (Sweetbox)
- Click "Lưu Loại Ghế"

#### 3. **Chỉnh Sửa Loại Ghế** (Edit)
- Từ danh sách, click icon ✏️ (edit)
- Cập nhật thông tin
- Click "Cập Nhật"

#### 4. **Xóa Loại Ghế** (Delete)
- Từ danh sách, click icon 🗑️ (delete)
- **Lưu ý**: Không thể xóa loại ghế đang được sử dụng trong các phòng chiếu

### Loại Ghế Mặc Định

Hệ thống đã tạo sẵn 3 loại ghế cơ bản:

```
1. Thường      - Giá phụ thu: 0 ₫
2. VIP         - Giá phụ thu: 20,000 ₫
3. Sweetbox    - Giá phụ thu: 40,000 ₫
```

---

## 🔧 Kiến Trúc & Cấu Trúc Thư Mục

```
filmgo/
├── app/
│   └── Http/
│       ├── Controllers/Admin/
│       │   └── SeatTypeController.php          # Controller quản lý loại ghế
│       └── Middleware/
│           └── AdminMiddleware.php              # Kiểm tra quyền admin
├── app/Models/
│   ├── SeatType.php                            # Model loại ghế
│   ├── User.php                                # Model người dùng
│   └── Role.php                                # Model vai trò
├── database/
│   ├── migrations/
│   │   ├── create_seat_types_table.php         # Bảng loại ghế
│   │   ├── create_roles_table.php              # Bảng vai trò
│   │   └── create_user_roles_table.php         # Bảng liên kết user-role
│   └── seeders/
│       ├── SeatTypeSeeder.php                  # Seed dữ liệu loại ghế
│       ├── RoleSeeder.php                      # Seed dữ liệu vai trò
│       └── UserSeeder.php                      # Seed dữ liệu người dùng
├── resources/views/admin/seat-types/
│   ├── index.blade.php                         # Danh sách loại ghế
│   ├── create.blade.php                        # Form tạo mới
│   └── edit.blade.php                          # Form chỉnh sửa
└── routes/
    └── web.php                                  # Routes khai báo
```

---

## 🚀 API Routes

Tất cả routes được bảo vệ bởi middleware `['auth', 'admin']`

```
GET    /admin/seat-types              - Danh sách loại ghế
GET    /admin/seat-types/create       - Form tạo mới
POST   /admin/seat-types              - Lưu loại ghế mới
GET    /admin/seat-types/{id}/edit    - Form chỉnh sửa
PUT    /admin/seat-types/{id}         - Cập nhật loại ghế
DELETE /admin/seat-types/{id}         - Xóa loại ghế
```

---

## 🛡️ Cơ Chế Bảo Mật

### AdminMiddleware
Kiểm tra xem người dùng có vai trò `admin`, `manager`, hoặc `staff` không
- Nếu không → Đăng xuất và chuyển hướng về trang login
- Nếu có → Cho phép tiếp tục

### Ví dụ Kiểm Tra Quyền

```php
// Trong controller hoặc view
@if(auth()->user()->roles()->whereIn('name', ['admin', 'manager'])->exists())
    <!-- Hiển thị chức năng -->
@endif
```

---

## 📊 Mô Hình Dữ Liệu

### Bảng: seat_types
```sql
CREATE TABLE seat_types (
    id BIGINT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    surcharge_price INT DEFAULT 0
);
```

### Bảng: roles
```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    description VARCHAR(255)
);
```

### Bảng: user_roles
```sql
CREATE TABLE user_roles (
    user_id BIGINT,
    role_id BIGINT,
    PRIMARY KEY (user_id, role_id)
);
```

### Relationships
```
User -> (many) Roles    (belongsToMany)
Role -> (many) Users    (belongsToMany)
SeatType -> (many) Seats (hasMany)
```

---

## 🧪 Testing

### 1. Chạy Migrations và Seeders
```bash
php artisan migrate --seed
```

### 2. Truy Cập Admin Portal
```
URL: http://localhost/admin/login
Email: admin@gmail.com
Password: 123456
```

### 3. Kiểm Tra Chức Năng
- ✅ Xem danh sách loại ghế
- ✅ Thêm loại ghế mới
- ✅ Chỉnh sửa loại ghế
- ✅ Xóa loại ghế (nếu không được sử dụng)

---

## ⚙️ Cấu Hình & Tùy Chỉnh

### Thêm Vai Trò Mới

Sửa file `database/seeders/RoleSeeder.php`:

```php
public function run(): void
{
    $roles = [
        // ... existing roles ...
        [
            'name' => 'supervisor',
            'description' => 'Người giám sát',
        ],
    ];

    foreach ($roles as $role) {
        Role::firstOrCreate(['name' => $role['name']], $role);
    }
}
```

### Thêm Quyền Chi Tiết (Permission)

Để mở rộng hệ thống với permissions chi tiết:

1. Tạo migration cho bảng permissions
2. Tạo Policy class cho SeatType
3. Sử dụng `can()` trong controller

Ví dụ:
```php
// Trong SeatTypeController
public function destroy(SeatType $seatType)
{
    $this->authorize('delete', $seatType);
    // ...
}
```

---

## 📝 Validation Rules

### Create/Update Seat Type

| Field | Rule | Thông Báo |
|-------|------|-----------|
| name | required, string, max:50, unique | Tên loại ghế không được để trống, max 50 ký tự |
| surcharge_price | required, integer, min:0 | Giá phụ thu phải là số >= 0 |

---

## 🐛 Troubleshooting

### Lỗi: "Tài khoản của bạn không có quyền truy cập"
- **Nguyên nhân**: Tài khoản không có vai trò admin/manager/staff
- **Giải pháp**: 
  1. Vào database, bảng `user_roles`
  2. Thêm role id = 1 (admin) cho user_id cần cấp quyền

### Lỗi: "Không thể xóa loại ghế đang được sử dụng"
- **Nguyên nhân**: Loại ghế này đang được dùng trong các ghế ở phòng chiếu
- **Giải pháp**: Xóa tất cả ghế đang sử dụng loại này trước, hoặc đổi loại ghế cho những ghế đó

### Không thấy nút "Quản Lý Loại Ghế" trong sidebar
- **Nguyên nhân**: Đã disable route trong web.php hoặc không có quyền
- **Giải pháp**: 
  1. Kiểm tra route có được khai báo
  2. Đảm bảo user có role admin/manager/staff

---

## 📚 Tài Liệu Liên Quan

- [Laravel Authorization](https://laravel.com/docs/authorization)
- [Laravel Relationships](https://laravel.com/docs/eloquent-relationships)
- [Middleware Documentation](https://laravel.com/docs/middleware)

---

## ✅ Checklist Triển Khai

- [x] Tạo SeatTypeController với CRUD
- [x] Tạo views (index, create, edit)
- [x] Thêm routes vào web.php
- [x] Cấu hình AdminMiddleware
- [x] Thêm navigation link trong sidebar
- [x] Seed dữ liệu mặc định
- [x] Validation rules
- [x] Error handling
- [x] Vi dụ quyền hạn
- [x] Tài liệu

---

## 🎯 Bước Tiếp Theo

Để mở rộng hệ thống, bạn có thể:

1. **Thêm Permissions Chi Tiết**
   - Tạo hệ thống permission granular
   - Kiểm soát ai có thể create/edit/delete

2. **Thêm Audit Log**
   - Ghi lại tất cả thay đổi loại ghế
   - Xem ai thực hiện hành động nào và khi nào

3. **Tạo API Public**
   - Cung cấp API cho mobile app
   - Lấy danh sách loại ghế từ API

4. **Dashboard Analytics**
   - Thống kê loại ghế được sử dụng nhiều nhất
   - Doanh thu theo loại ghế

---

**Tạo bởi**: GitHub Copilot  
**Phiên bản**: 1.0  
**Cập nhật lần cuối**: 2026-06-19
