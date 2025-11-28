<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

try {
    // Xóa các bảng cũ nếu tồn tại (để reset database)
    $db->exec("DROP TABLE IF EXISTS CHITIET_BAOCAO");
    $db->exec("DROP TABLE IF EXISTS BAOCAO_DOANHTHU");
    $db->exec("DROP TABLE IF EXISTS CHITIET_HOADON");
    $db->exec("DROP TABLE IF EXISTS HOADON");
    $db->exec("DROP TABLE IF EXISTS CHITIET_THUE");
    $db->exec("DROP TABLE IF EXISTS PHIEUTHUE");
    $db->exec("DROP TABLE IF EXISTS KHACHHANG");
    $db->exec("DROP TABLE IF EXISTS PHONG");
    $db->exec("DROP TABLE IF EXISTS LOAIPHONG");
    $db->exec("DROP TABLE IF EXISTS THAMSO");

    // 1. Bảng LOAIPHONG (Quản lý Loại Phòng)
    $db->exec("CREATE TABLE LOAIPHONG (
        MaLoaiPhong INTEGER PRIMARY KEY AUTOINCREMENT,
        TenLoai TEXT NOT NULL UNIQUE,
        DonGiaCoBan REAL NOT NULL CHECK(DonGiaCoBan > 0)
    )");

    // 2. Bảng PHONG (Quản lý Phòng)
    $db->exec("CREATE TABLE PHONG (
        MaPhong INTEGER PRIMARY KEY AUTOINCREMENT,
        SoPhong TEXT NOT NULL UNIQUE,
        MaLoaiPhong INTEGER NOT NULL,
        TinhTrang TEXT DEFAULT 'Trống' CHECK(TinhTrang IN ('Trống', 'Đã thuê')),
        GhiChu TEXT,
        FOREIGN KEY (MaLoaiPhong) REFERENCES LOAIPHONG(MaLoaiPhong) ON DELETE RESTRICT ON UPDATE CASCADE
    )");

    // 3. Bảng KHACHHANG (Quản lý Khách hàng)
    $db->exec("CREATE TABLE KHACHHANG (
        MaKhachHang INTEGER PRIMARY KEY AUTOINCREMENT,
        TenKhach TEXT NOT NULL,
        LoaiKhach TEXT NOT NULL CHECK(LoaiKhach IN ('Nội địa', 'Nước ngoài')),
        CMND TEXT UNIQUE,
        DiaChi TEXT
    )");

    // 4. Bảng PHIEUTHUE (Quản lý Phiếu Thuê)
    $db->exec("CREATE TABLE PHIEUTHUE (
        MaPhieuThue INTEGER PRIMARY KEY AUTOINCREMENT,
        MaPhong INTEGER NOT NULL,
        NgayBatDauThue DATE NOT NULL DEFAULT (date('now')),
        TinhTrangPhieu TEXT DEFAULT 'Đang thuê' CHECK(TinhTrangPhieu IN ('Đang thuê', 'Đã thanh toán', 'Đã hủy')),
        FOREIGN KEY (MaPhong) REFERENCES PHONG(MaPhong) ON DELETE RESTRICT ON UPDATE CASCADE
    )");

    // 5. Bảng CHITIET_THUE (Chi tiết Phiếu Thuê - Quan hệ n-n)
    $db->exec("CREATE TABLE CHITIET_THUE (
        MaPhieuThue INTEGER NOT NULL,
        MaKhachHang INTEGER NOT NULL,
        PRIMARY KEY (MaPhieuThue, MaKhachHang),
        FOREIGN KEY (MaPhieuThue) REFERENCES PHIEUTHUE(MaPhieuThue) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (MaKhachHang) REFERENCES KHACHHANG(MaKhachHang) ON DELETE RESTRICT ON UPDATE CASCADE
    )");

    // 6. Bảng HOADON (Quản lý Hóa đơn)
    $db->exec("CREATE TABLE HOADON (
        MaHoaDon INTEGER PRIMARY KEY AUTOINCREMENT,
        MaPhieuThue INTEGER NOT NULL UNIQUE,
        TenKhachHangCoQuan TEXT NOT NULL,
        DiaChiThanhToan TEXT,
        NgayThanhToan DATE NOT NULL DEFAULT (date('now')),
        TriGia REAL NOT NULL CHECK(TriGia >= 0),
        FOREIGN KEY (MaPhieuThue) REFERENCES PHIEUTHUE(MaPhieuThue) ON DELETE RESTRICT ON UPDATE CASCADE
    )");

    // 7. Bảng CHITIET_HOADON (Chi tiết Hóa đơn)
    $db->exec("CREATE TABLE CHITIET_HOADON (
        MaHoaDon INTEGER NOT NULL,
        MaPhong INTEGER NOT NULL,
        SoNgayThue INTEGER NOT NULL CHECK(SoNgayThue > 0),
        DonGiaTinh REAL NOT NULL CHECK(DonGiaTinh > 0),
        ThanhTien REAL NOT NULL CHECK(ThanhTien >= 0),
        PRIMARY KEY (MaHoaDon, MaPhong),
        FOREIGN KEY (MaHoaDon) REFERENCES HOADON(MaHoaDon) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (MaPhong) REFERENCES PHONG(MaPhong) ON DELETE RESTRICT ON UPDATE CASCADE
    )");

    // 8. Bảng BAOCAO_DOANHTHU (Báo cáo Doanh thu)
    $db->exec("CREATE TABLE BAOCAO_DOANHTHU (
        MaBaoCao INTEGER PRIMARY KEY AUTOINCREMENT,
        Thang INTEGER NOT NULL CHECK(Thang BETWEEN 1 AND 12),
        Nam INTEGER NOT NULL CHECK(Nam > 2000),
        NgayLap DATE NOT NULL DEFAULT (date('now')),
        UNIQUE(Thang, Nam)
    )");

    // 9. Bảng CHITIET_BAOCAO (Chi tiết Báo cáo)
    $db->exec("CREATE TABLE CHITIET_BAOCAO (
        MaBaoCao INTEGER NOT NULL,
        MaLoaiPhong INTEGER NOT NULL,
        DoanhThu REAL NOT NULL DEFAULT 0 CHECK(DoanhThu >= 0),
        TyLe REAL NOT NULL DEFAULT 0 CHECK(TyLe >= 0 AND TyLe <= 100),
        PRIMARY KEY (MaBaoCao, MaLoaiPhong),
        FOREIGN KEY (MaBaoCao) REFERENCES BAOCAO_DOANHTHU(MaBaoCao) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (MaLoaiPhong) REFERENCES LOAIPHONG(MaLoaiPhong) ON DELETE RESTRICT ON UPDATE CASCADE
    )");

    // 10. Bảng THAMSO (Tham số Hệ thống - QĐ6)
    $db->exec("CREATE TABLE THAMSO (
        TenThamSo TEXT PRIMARY KEY,
        GiaTri REAL NOT NULL,
        MoTa TEXT
    )");

    // Tạo các index để tăng hiệu suất truy vấn
    $db->exec("CREATE INDEX idx_phong_tinhtrang ON PHONG(TinhTrang)");
    $db->exec("CREATE INDEX idx_phong_loai ON PHONG(MaLoaiPhong)");
    $db->exec("CREATE INDEX idx_phieuthue_phong ON PHIEUTHUE(MaPhong)");
    $db->exec("CREATE INDEX idx_phieuthue_tinhtrang ON PHIEUTHUE(TinhTrangPhieu)");
    $db->exec("CREATE INDEX idx_hoadon_ngay ON HOADON(NgayThanhToan)");
    $db->exec("CREATE INDEX idx_khachhang_loai ON KHACHHANG(LoaiKhach)");

    echo "<h3>✓ Tạo cấu trúc bảng thành công!</h3>";

    // Thêm dữ liệu mẫu cho LOAIPHONG
    $stmt = $db->prepare("INSERT INTO LOAIPHONG (TenLoai, DonGiaCoBan) VALUES (?, ?)");
    $loaiPhongs = [
        ['Loại A', 300000],
        ['Loại B', 500000],
        ['Loại C', 800000]
    ];
    foreach ($loaiPhongs as $lp) {
        $stmt->execute($lp);
    }
    echo "✓ Thêm " . count($loaiPhongs) . " loại phòng<br>";

    // Thêm dữ liệu mẫu cho PHONG
    $stmt = $db->prepare("INSERT INTO PHONG (SoPhong, MaLoaiPhong, TinhTrang, GhiChu) VALUES (?, ?, ?, ?)");
    $phongs = [
        ['101', 1, 'Trống', 'Phòng sạch sẽ'],
        ['102', 1, 'Trống', NULL],
        ['103', 1, 'Trống', NULL],
        ['201', 2, 'Trống', NULL],
        ['202', 2, 'Trống', NULL],
        ['203', 2, 'Trống', NULL],
        ['301', 3, 'Trống', 'Phòng VIP view biển'],
        ['302', 3, 'Trống', 'Phòng VIP'],
        ['303', 3, 'Trống', NULL]
    ];
    foreach ($phongs as $p) {
        $stmt->execute($p);
    }
    echo "✓ Thêm " . count($phongs) . " phòng<br>";

    // Thêm Tham số Hệ thống (QĐ6)
    $stmt = $db->prepare("INSERT INTO THAMSO (TenThamSo, GiaTri, MoTa) VALUES (?, ?, ?)");
    $thamSos = [
        ['SO_KHACH_TOI_DA', 3, 'Số lượng khách tối đa trong 1 phòng (QĐ2)'],
        ['TL_PHU_THU_KHACH_3', 0.25, 'Tỉ lệ phụ thu khi có khách thứ 3 - 25% (QĐ4)'],
        ['HS_KHACH_NUOC_NGOAI', 1.5, 'Hệ số nhân đơn giá khi có khách nước ngoài - 1.5 (QĐ4)'],
        ['GIOI_HAN_DOANH_THU', 5000000, 'Ngưỡng doanh thu tối thiểu cho báo cáo']
    ];
    foreach ($thamSos as $ts) {
        $stmt->execute($ts);
    }
    echo "✓ Thêm " . count($thamSos) . " tham số hệ thống<br>";

    // Thêm dữ liệu mẫu KHACHHANG
    $stmt = $db->prepare("INSERT INTO KHACHHANG (TenKhach, LoaiKhach, CMND, DiaChi) VALUES (?, ?, ?, ?)");
    $khachHangs = [
        ['Nguyễn Văn An', 'Nội địa', '123456789', 'Hà Nội'],
        ['Trần Thị Bình', 'Nội địa', '987654321', 'TP.HCM'],
        ['Lê Văn Cường', 'Nội địa', '456789123', 'Đà Nẵng'],
        ['John Smith', 'Nước ngoài', 'AB123456', 'USA'],
        ['Mary Johnson', 'Nước ngoài', 'CD789012', 'UK'],
        ['Phạm Thị Dung', 'Nội địa', '321654987', 'Hải Phòng']
    ];
    foreach ($khachHangs as $kh) {
        $stmt->execute($kh);
    }
    echo "✓ Thêm " . count($khachHangs) . " khách hàng mẫu<br>";

    // Thêm dữ liệu mẫu PHIEUTHUE và CHITIET_THUE
    $stmt = $db->prepare("INSERT INTO PHIEUTHUE (MaPhong, NgayBatDauThue, TinhTrangPhieu) VALUES (?, ?, ?)");
    $stmt->execute([1, date('Y-m-d', strtotime('-5 days')), 'Đang thuê']);
    $maPhieuThue1 = $db->lastInsertId();
    
    $stmt = $db->prepare("INSERT INTO CHITIET_THUE (MaPhieuThue, MaKhachHang) VALUES (?, ?)");
    $stmt->execute([$maPhieuThue1, 1]);
    $stmt->execute([$maPhieuThue1, 2]);
    
    // Cập nhật tình trạng phòng
    $db->exec("UPDATE PHONG SET TinhTrang = 'Đã thuê' WHERE MaPhong = 1");
    
    echo "✓ Thêm dữ liệu mẫu phiếu thuê<br>";

    echo "<h3 style='color: green;'>✓ Khởi tạo database hoàn tất!</h3>";
    echo "<p><strong>File database:</strong> " . __DIR__ . '/hotel.db</p>';
    echo "<p><strong>Tổng số bảng:</strong> 10 bảng</p>";
    echo "<p><strong>Các ràng buộc:</strong> Primary Key, Foreign Key, Check constraints, Unique constraints</p>";
    echo "<p><strong>Tối ưu hóa:</strong> 6 indexes được tạo</p>";

} catch(PDOException $e) {
    echo "<h3 style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</h3>";
    die();
}
?>
