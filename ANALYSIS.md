# Phân tích đồ án - Hệ thống Quản lý Khách sạn

## Tổng quan
Đây là một ứng dụng quản lý khách sạn dạng web (PHP + SQLite) theo kiến trúc MVC nhẹ:
- Giao diện: HTML/CSS trong các file root và `pages/`.
- Controller: `controllers/` chứa logic chính như `AuthController`, `PhongController`, `PhieuThueController`, `HoaDonController`, `BaoCaoController`, `KhachHangController`.
- Model/DB: `config/database.php` (kết nối SQLite) và `database/init.php` khởi tạo schema.

## Chức năng chính
- 🛏️ Quản lý loại phòng và phòng (tạo, xem, cập nhật trạng thái)
- 🎫 Đặt phòng (tạo phiếu thuê, liên kết khách)
- 💎 Lập hóa đơn theo quy tắc phụ thu/khách nước ngoài
- 🔎 Tra cứu đặt phòng theo mã phiếu/CMND/tên
- 📈 Báo cáo doanh thu theo tháng/năm
- 👤 Quản lý tài khoản người dùng (Admin, Nhân viên)

## Cấu trúc DB chính
- LOAIPHONG, PHONG
- KHACHHANG, PHIEUTHUE, CHITIET_THUE
- HOADON, CHITIET_HOADON
- BAOCAO_DOANHTHU, CHITIET_BAOCAO
- THAMSO (lưu tham số hệ thống như `SO_KHACH_TOI_DA`, `TL_PHU_THU_KHACH_3`, `HS_KHACH_NUOC_NGOAI`)
- NGUOIDUNG (quản trị/nhân viên)

## Sự cố thường gặp & cách xử lý
- Lỗi: `SQLSTATE[HY000]: General error: 1 no such table: NGUOIDUNG` → Nguyên nhân: database chưa khởi tạo, hoặc `init.php` dùng cú pháp MySQL không tương thích với SQLite.
- Cách khắc phục: Chạy `php database/init.php` từ thư mục project để tạo lại database SQLite (`database/hotel.db`).
- Cảnh báo: `init.php` hiện có các lệnh DROP TABLE để reset DB. Không chạy nếu bạn muốn giữ dữ liệu hiện có.

## Thiết lập & Khởi chạy (macOS)
1. Cài PHP (nếu chưa có):
```bash
brew install php
```
2. Khởi tạo DB (chỉ lần đầu hoặc khi muốn reset):
```bash
cd /Users/nguyenthien_/Documents/WEB-KhachSan-NMCNPM
php database/init.php
```
3. Chạy server PHP built-in:
```bash
php -S localhost:8000
```
4. Truy cập giao diện người dùng: `http://localhost:8000`
5. Trang admin: `http://localhost:8000/admin/login.php` (mặc định có tài khoản `admin` / `admin123` nếu `init.php` đã chèn)

## Sửa lỗi đã thực hiện
- `database/init.php` đã được chỉnh sửa để tương thích với SQLite:
  - Thay `INT AUTO_INCREMENT` / `ENGINE` / `ENUM` bằng các kiểu và mặc định SQLite (INTEGER PRIMARY KEY AUTOINCREMENT, TEXT...)
  - Thay `INSERT IGNORE` bằng `INSERT OR IGNORE`.

## Bảo mật & cải tiến đề xuất
- Bảo mật đầu vào: Mặc dù sử dụng prepared statements, cần validate và sanitize tất cả input trên form (client & server).
- CSRF: Thêm token CSRF cho mọi biểu mẫu quan trọng: đăng nhập, đặt phòng, lập hóa đơn, quản trị.
- XSS: Escape output khi render vào HTML (dùng `htmlspecialchars` hoặc thư viện template an toàn).
- Mật khẩu: Áp dụng chính sách mật khẩu mạnh, hạn chế số lần thử và hỗ trợ reset mật khẩu an toàn.
- Mã hoá/ít lộ thông tin: Ẩn thông tin nhạy cảm trong lỗi (không log lỗi DB ra người dùng), logging an toàn.
- Logging & Audit: Thêm logging cho hoạt động quản trị, thay đổi trạng thái phòng, lập hóa đơn.
- Phân quyền: Kiểm tra kĩ `AuthController::requireAdmin()` trước các thao tác nhạy cảm.
- Test & CI: Thêm unit-tests cho logic giá/thuế/phụ thu, và test end-to-end cho quy trình đặt phòng → hóa đơn.

## Hiệu năng & dữ liệu lớn
- Với SQLite hiện tại, phân vùng viết đọc lớn dễ gây tắc. Nếu hệ thống mở rộng, cân nhắc chuyển sang MySQL/Postgres.
- Sử dụng index phù hợp (đã có một số index trong `init.php`).

## Hướng phát triển tiếp theo (gợi ý)
- API RESTful cho frontend/tích hợp 3rd-party.
- Giao diện bảng điều khiển admin hiện đại (SPA) với trạng thái thời gian thực.
- Import/Export báo cáo, và lịch sử giao dịch.

---
Tôi có thể tạo thêm sơ đồ ER cơ sở dữ liệu hoặc tóm tắt luồng nghiệp vụ tùy bạn muốn. Muốn tiếp theo tôi bổ sung mục nào?
