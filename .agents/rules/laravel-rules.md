---
trigger: always_on
---

# Quy tắc phát triển dự án Laravel (FilmGo)

1. **Chuẩn viết code PHP**:
   - Mọi mã nguồn PHP phải tuân thủ chuẩn viết code PSR-12.

2. **Cấu trúc mã nguồn & Logic nghiệp vụ**:
   - Không được viết logic nghiệp vụ xử lý dữ liệu phức tạp trực tiếp trong file Controller.
   - Phải tạo các lớp Service riêng trong thư mục `app/Services/` để xử lý logic nghiệp vụ.

3. **Giao diện & Styling**:
   - Frontend sử dụng Vue.js 3.
   -Giao diện được xây dựng bằng Tailwind CSS.
   -Thiết kế Responsive, tương thích trên điện thoại, máy tính bảng và máy tính.
   -Ưu tiên tái sử dụng Component để dễ bảo trì và mở rộng.
4. Bảo mật và hiệu năng
   -Thực hiện Validate dữ liệu đầu vào.
   -Sử dụng Authentication, Authorization và Middleware của Laravel.
   -Tối ưu truy vấn dữ liệu, sử dụng Pagination, Cache và Queue khi cần thiết.