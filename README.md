# Hệ thống Quản lý Khách sạn

## 🚀 KHỞI ĐỘNG NHANH

### Cách 1: Double-click file start.bat (NHANH NHẤT)
```
1. Double-click file start.bat
2. Đợi server khởi động
3. Truy cập: http://localhost:8000
```

### Cách 2: Dùng Command Line
```bash
# Bước 1: Mở CMD tại thư mục dự án
cd "c:\Users\Duc Thinh\Documents\Nhập môn CNPM\DOAN\WEB-KhachSan-NMCNPM"

# Bước 2: Khởi tạo database (chỉ lần đầu)
# LƯU Ý: `database/init.php` sẽ xóa các bảng cũ nếu tồn tại (reset DB).
# Sao lưu database/hotel.db trước khi chạy nếu bạn muốn giữ dữ liệu hiện có.
php database/init.php

# Bước 3: Chạy server
php -S localhost:8000

# Bước 4: Truy cập
# http://localhost:8000
```

## ⚠️ GẶP LỖI "NOT FOUND"?

**Đọc ngay:** [FIX-ERROR.md](FIX-ERROR.md)

**Hoặc chạy file kiểm tra:**
```
http://localhost:8000/troubleshoot.php
```

## Chức năng

- ✅ Quản lý phòng (YCC 1)
- ✅ Cho thuê phòng (YCC 2)
- ✅ Tra cứu phòng (YCC 3)
- ✅ Lập hóa đơn (YCC 4, QĐ4)
- ✅ Báo cáo doanh thu (YCC 5)
- ✅ Quản lý tham số (YCC 6, QĐ6)

## Cấu trúc Database

- LOAIPHONG, PHONG
- KHACHHANG, PHIEUTHUE, CHITIET_THUE
- HOADON, CHITIET_HOADON
- BAOCAO_DOANHTHU, CHITIET_BAOCAO
- THAMSO

## 🔐 CẤU HÌNH OAUTH

### ✅ Google OAuth (Đã sẵn sàng)

### Credentials hiện tại:

```
Client ID: 416938682838-6ohqmd704l8v07ved380didth1feauqm.apps.googleusercontent.com
Client Secret: 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
Redirect URI: http://localhost:8000/customer/oauth-callback.php
```

### Kiểm tra cấu hình:

```bash
# Bước 1: Truy cập tool kiểm tra
http://localhost:8000/customer/verify-oauth-credentials.php

# Bước 2: Nếu OK, test đăng nhập
http://localhost:8000/customer/login.php
```

### ⚠️ QUAN TRỌNG:

1. **Redirect URI trên Google Console phải là:**
   ```
   http://localhost:8000/customer/oauth-callback.php
   ```

2. **Nếu vẫn gặp lỗi 401:**
   - Đợi 5-10 phút để Google cập nhật
   - Clear cache browser (Ctrl+Shift+Del)
   - Restart PHP server

3. **Khi deploy lên production:**
   - Cập nhật Redirect URI thành: `https://yourdomain.com/customer/oauth-callback.php`
   - Thêm URI mới vào Google Console
   - Cập nhật `config/production.php`

### Troubleshooting:

**Lỗi "redirect_uri_mismatch":**
- Check Google Console có đúng URI: `http://localhost:8000/customer/oauth-callback.php`
- Không có space, không có trailing slash

**Lỗi "invalid_client":**
- Đã fix! Client ID và Secret đã đúng
- Nếu vẫn lỗi → Clear cache và thử lại sau 5 phút

**Lỗi "access_denied":**
- User từ chối quyền truy cập
- Thử đăng nhập lại và click "Allow"

## 🔑 ĐĂNG NHẬP HỆ THỐNG

### Tài khoản Quản trị viên (Admin)

**Truy cập:** http://localhost:8000/admin/login.php
