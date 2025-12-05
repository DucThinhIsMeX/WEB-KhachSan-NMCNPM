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

