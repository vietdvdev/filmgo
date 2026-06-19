# 🚀 Quick Start Guide - Quản Lý Loại Ghế

## Khởi Động Nhanh (5 phút)

### 1️⃣ Chạy Database Setup
```bash
cd d:\laragon\www\filmgo
php artisan migrate --seed
```

### 2️⃣ Truy Cập Admin
```
URL: http://localhost/admin/login
Email: admin@gmail.com
Password: 123456
```

### 3️⃣ Vào Quản Lý Loại Ghế
```
Sidebar → "Quản Lý Loại Ghế"
hoặc: http://localhost/admin/seat-types
```

---

## 🎯 Thao Tác Cơ Bản

### 📖 Xem Danh Sách
```
GET /admin/seat-types
```
- Liệt kê tất cả loại ghế
- Tìm kiếm theo tên
- Phân trang 10 items/trang

### ➕ Tạo Mới
```
GET /admin/seat-types/create  → Hiển thị form
POST /admin/seat-types        → Lưu dữ liệu
```

**Form fields:**
- `name` - Tên loại ghế (bắt buộc, max 50)
- `surcharge_price` - Giá phụ thu (bắt buộc, >= 0)

**Ví dụ:**
```
Thường      | 0
VIP         | 20000
Sweetbox    | 40000
Ghế đôi     | 50000
```

### ✏️ Sửa
```
GET  /admin/seat-types/{id}/edit  → Form sửa
PUT  /admin/seat-types/{id}        → Lưu thay đổi
```

### 🗑️ Xóa
```
DELETE /admin/seat-types/{id}
```
⚠️ Chỉ xóa được nếu không có ghế nào sử dụng

---

## 🔐 Quyền Hạn

### Cần Có Vai Trò
- `admin` - Quản trị viên
- `manager` - Quản lý rạp
- `staff` - Nhân viên (view only)

### Kiểm Tra Quyền
```blade
@if(auth()->user()->roles()->whereIn('name', ['admin', 'manager'])->exists())
    <!-- Cho phép xóa -->
@endif
```

---

## 📊 Mô Hình Dữ Liệu

### SeatType Model
```php
class SeatType extends Model {
    public $timestamps = false;
    protected $fillable = ['name', 'surcharge_price'];
    
    public function seats() {
        return $this->hasMany(Seat::class);
    }
}
```

### Database Schema
```sql
CREATE TABLE seat_types (
    id BIGINT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,
    surcharge_price INT DEFAULT 0
);
```

---

## 💻 Code Snippets

### Controller Method
```php
// app/Http/Controllers/Admin/SeatTypeController.php

public function index(Request $request) {
    $query = SeatType::withCount('seats');
    
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }
    
    $seatTypes = $query->orderBy('id', 'desc')->paginate(10);
    return view('admin.seat-types.index', compact('seatTypes'));
}
```

### View - Display Table
```blade
@foreach($seatTypes as $seatType)
    <tr>
        <td>{{ $seatType->name }}</td>
        <td>{{ number_format($seatType->surcharge_price) }} ₫</td>
        <td>{{ $seatType->seats_count }}</td>
        <td>
            <a href="{{ route('admin.seat-types.edit', $seatType->id) }}">Sửa</a>
            <form action="{{ route('admin.seat-types.destroy', $seatType->id) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit">Xóa</button>
            </form>
        </td>
    </tr>
@endforeach
```

### Form Validation
```php
$request->validate([
    'name' => 'required|string|max:50|unique:seat_types,name,' . $seatType->id,
    'surcharge_price' => 'required|integer|min:0',
]);
```

---

## 🧪 Testing

### Unit Test
```bash
php artisan make:test SeatTypeTest
```

### Feature Test
```php
// tests/Feature/SeatTypeTest.php
public function test_can_create_seat_type() {
    $this->actingAs($admin)
        ->post('/admin/seat-types', [
            'name' => 'Premium',
            'surcharge_price' => 50000,
        ])
        ->assertRedirect('/admin/seat-types');
}
```

---

## 🐛 Debug Tips

### Xem Routes
```bash
php artisan route:list | grep seat
```

### Xem Logs
```bash
tail -f storage/logs/laravel.log
```

### Database Query
```php
DB::enableQueryLog();
SeatType::all();
dd(DB::getQueryLog());
```

---

## 🔗 URLs

```
Dashboard      → /admin/dashboard
Seat Types     → /admin/seat-types
Edit Seat      → /admin/seat-types/1/edit
Genres         → /admin/genres
Movies         → /admin/movies
Users          → /admin/users
Logout         → /admin/logout (POST)
```

---

## 🛠️ Troubleshooting

| Lỗi | Nguyên nhân | Giải pháp |
|-----|-----------|----------|
| 404 Not Found | Route không tìm thấy | `php artisan route:list` |
| 403 Forbidden | Không có quyền | Kiểm tra role của user |
| SQLSTATE[42S02] | Table không tồn tại | `php artisan migrate` |
| Validation fails | Dữ liệu không hợp lệ | Xem `$request->errors()` |

---

## 📱 API Response

### Success Response
```json
{
    "data": [
        {
            "id": 1,
            "name": "Thường",
            "surcharge_price": 0,
            "seats_count": 48
        }
    ],
    "pagination": {
        "total": 3,
        "per_page": 10,
        "current_page": 1
    }
}
```

### Error Response
```json
{
    "message": "Validation failed",
    "errors": {
        "name": ["Tên loại ghế không được để trống"]
    }
}
```

---

## 📚 Related Files

- `SEAT_TYPES_ADMIN_GUIDE.md` - Hướng dẫn chi tiết
- `IMPLEMENTATION_SUMMARY.md` - Tóm tắt triển khai
- `app/Http/Controllers/Admin/SeatTypeController.php` - Controller
- `resources/views/admin/seat-types/` - Views

---

## 🎓 Học Thêm

- [Laravel Resource Controllers](https://laravel.com/docs/controllers#resource-controllers)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Blade Templates](https://laravel.com/docs/blade)
- [Form Validation](https://laravel.com/docs/validation)

---

**Version**: 1.0  
**Last Updated**: 2026-06-19  
**Status**: ✅ Ready to Use
