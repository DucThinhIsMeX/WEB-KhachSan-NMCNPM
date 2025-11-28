# Hệ thống Quản lý Khách sạn

## Cài đặt

1. Khởi tạo database:
```bash
php database/init.php
```

2. Chạy server PHP:
```bash
php -S localhost:8000
```

3. Truy cập: http://localhost:8000

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

