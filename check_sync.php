<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Kiểm tra đồng bộ Database</title>
    <style>
        body { 
            font-family: Arial; 
            padding: 40px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1200px; 
            background: white; 
            padding: 40px; 
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin: 0 auto;
        }
        h1, h2 { color: #667eea; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: #667eea; 
            color: white; 
        }
        tr:nth-child(even) { background: #f8f9fa; }
        .section { 
            margin: 30px 0; 
            padding: 20px; 
            background: #f8f9ff; 
            border-radius: 10px; 
            border-left: 4px solid #667eea;
        }
        .badge { 
            display: inline-block; 
            padding: 5px 10px; 
            border-radius: 5px; 
            font-size: 0.9em; 
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Kiểm tra Đồng bộ Database & Trang Web</h1>";

// 1. Kiểm tra cấu trúc bảng
echo "<div class='section'>";
echo "<h2>📊 1. Cấu trúc Database</h2>";
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
echo "<p><strong>Tổng số bảng:</strong> " . count($tables) . "</p>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";
echo "</div>";

// 2. Kiểm tra dữ liệu LOAIPHONG
echo "<div class='section'>";
echo "<h2>🏷️ 2. Loại Phòng (LOAIPHONG)</h2>";
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG ORDER BY TenLoai")->fetchAll();
echo "<table>";
echo "<tr><th>Mã</th><th>Tên Loại</th><th>Đơn Giá Cơ Bản</th></tr>";
foreach ($loaiPhongs as $lp) {
    echo "<tr>";
    echo "<td>{$lp['MaLoaiPhong']}</td>";
    echo "<td>{$lp['TenLoai']}</td>";
    echo "<td>" . number_format($lp['DonGiaCoBan']) . " VNĐ</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 3. Kiểm tra dữ liệu PHONG
echo "<div class='section'>";
echo "<h2>🛏️ 3. Danh Sách Phòng (PHONG)</h2>";
$phongs = $db->query("SELECT P.*, L.TenLoai FROM PHONG P 
                      JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                      ORDER BY P.SoPhong")->fetchAll();
echo "<table>";
echo "<tr><th>Mã</th><th>Số Phòng</th><th>Loại</th><th>Tình Trạng</th><th>Ghi Chú</th></tr>";
$phongTrong = 0;
$phongDaThue = 0;
foreach ($phongs as $p) {
    $badge = $p['TinhTrang'] == 'Trống' ? 'badge-success' : 'badge-danger';
    if ($p['TinhTrang'] == 'Trống') $phongTrong++;
    else $phongDaThue++;
    
    echo "<tr>";
    echo "<td>{$p['MaPhong']}</td>";
    echo "<td><strong>{$p['SoPhong']}</strong></td>";
    echo "<td>{$p['TenLoai']}</td>";
    echo "<td><span class='badge $badge'>{$p['TinhTrang']}</span></td>";
    echo "<td>" . ($p['GhiChu'] ?? '-') . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Tổng:</strong> " . count($phongs) . " phòng | ";
echo "<span class='success'>Trống: $phongTrong</span> | ";
echo "<span class='error'>Đã thuê: $phongDaThue</span></p>";
echo "</div>";

// 4. Kiểm tra THAMSO
echo "<div class='section'>";
echo "<h2>⚙️ 4. Tham Số Hệ Thống (THAMSO)</h2>";
$thamSos = $db->query("SELECT * FROM THAMSO ORDER BY TenThamSo")->fetchAll();
echo "<table>";
echo "<tr><th>Tên Tham Số</th><th>Giá Trị</th><th>Mô Tả</th></tr>";
foreach ($thamSos as $ts) {
    echo "<tr>";
    echo "<td><strong>{$ts['TenThamSo']}</strong></td>";
    echo "<td>{$ts['GiaTri']}</td>";
    echo "<td>{$ts['MoTa']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 5. Kiểm tra PHIEUTHUE
echo "<div class='section'>";
echo "<h2>📝 5. Phiếu Thuê (PHIEUTHUE)</h2>";
$phieuThues = $db->query("SELECT PT.*, P.SoPhong FROM PHIEUTHUE PT 
                          JOIN PHONG P ON PT.MaPhong = P.MaPhong 
                          ORDER BY PT.NgayBatDauThue DESC")->fetchAll();
if (count($phieuThues) > 0) {
    echo "<table>";
    echo "<tr><th>Mã</th><th>Phòng</th><th>Ngày Bắt Đầu</th><th>Tình Trạng</th></tr>";
    foreach ($phieuThues as $pt) {
        $badge = $pt['TinhTrangPhieu'] == 'Đang thuê' ? 'badge-warning' : 
                ($pt['TinhTrangPhieu'] == 'Đã thanh toán' ? 'badge-success' : 'badge-danger');
        echo "<tr>";
        echo "<td>{$pt['MaPhieuThue']}</td>";
        echo "<td>{$pt['SoPhong']}</td>";
        echo "<td>{$pt['NgayBatDauThue']}</td>";
        echo "<td><span class='badge $badge'>{$pt['TinhTrangPhieu']}</span></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ Chưa có phiếu thuê nào</p>";
}
echo "</div>";

// 6. Kiểm tra HOADON
echo "<div class='section'>";
echo "<h2>💵 6. Hóa Đơn (HOADON)</h2>";
$hoaDons = $db->query("SELECT H.*, P.SoPhong FROM HOADON H 
                       JOIN PHIEUTHUE PT ON H.MaPhieuThue = PT.MaPhieuThue 
                       JOIN PHONG P ON PT.MaPhong = P.MaPhong 
                       ORDER BY H.NgayThanhToan DESC")->fetchAll();
if (count($hoaDons) > 0) {
    echo "<table>";
    echo "<tr><th>Mã</th><th>Phòng</th><th>Khách Hàng</th><th>Ngày TT</th><th>Trị Giá</th></tr>";
    $tongDoanhThu = 0;
    foreach ($hoaDons as $hd) {
        $tongDoanhThu += $hd['TriGia'];
        echo "<tr>";
        echo "<td>{$hd['MaHoaDon']}</td>";
        echo "<td>{$hd['SoPhong']}</td>";
        echo "<td>{$hd['TenKhachHangCoQuan']}</td>";
        echo "<td>{$hd['NgayThanhToan']}</td>";
        echo "<td><strong>" . number_format($hd['TriGia']) . " VNĐ</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Tổng doanh thu:</strong> <span class='success'>" . number_format($tongDoanhThu) . " VNĐ</span></p>";
} else {
    echo "<p class='warning'>⚠️ Chưa có hóa đơn nào</p>";
}
echo "</div>";

// 7. Kiểm tra đồng bộ giữa PHONG và PHIEUTHUE
echo "<div class='section'>";
echo "<h2>🔄 7. Kiểm Tra Đồng Bộ</h2>";
echo "<h3>7.1. Tình trạng phòng vs Phiếu thuê</h3>";

$phongDangThue = $db->query("SELECT P.SoPhong, P.TinhTrang, PT.TinhTrangPhieu 
                             FROM PHONG P 
                             LEFT JOIN PHIEUTHUE PT ON P.MaPhong = PT.MaPhong AND PT.TinhTrangPhieu = 'Đang thuê'")->fetchAll();

$errors = [];
foreach ($phongDangThue as $p) {
    if ($p['TinhTrang'] == 'Đã thuê' && !$p['TinhTrangPhieu']) {
        $errors[] = "Phòng {$p['SoPhong']} đánh dấu 'Đã thuê' nhưng không có phiếu thuê đang hoạt động";
    }
    if ($p['TinhTrang'] == 'Trống' && $p['TinhTrangPhieu'] == 'Đang thuê') {
        $errors[] = "Phòng {$p['SoPhong']} đánh dấu 'Trống' nhưng có phiếu thuê đang hoạt động";
    }
}

if (count($errors) == 0) {
    echo "<p class='success'>✅ Tất cả phòng đồng bộ với phiếu thuê</p>";
} else {
    echo "<p class='error'>❌ Phát hiện " . count($errors) . " lỗi đồng bộ:</p>";
    echo "<ul>";
    foreach ($errors as $err) {
        echo "<li class='error'>$err</li>";
    }
    echo "</ul>";
}

// 7.2. Kiểm tra foreign key
echo "<h3>7.2. Kiểm tra ràng buộc dữ liệu</h3>";
$fkErrors = [];

// Kiểm tra PHONG -> LOAIPHONG
$invalidPhong = $db->query("SELECT P.SoPhong FROM PHONG P 
                            LEFT JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                            WHERE L.MaLoaiPhong IS NULL")->fetchAll();
if (count($invalidPhong) > 0) {
    foreach ($invalidPhong as $p) {
        $fkErrors[] = "Phòng {$p['SoPhong']} tham chiếu đến loại phòng không tồn tại";
    }
}

// Kiểm tra PHIEUTHUE -> PHONG
$invalidPT = $db->query("SELECT PT.MaPhieuThue FROM PHIEUTHUE PT 
                         LEFT JOIN PHONG P ON PT.MaPhong = P.MaPhong 
                         WHERE P.MaPhong IS NULL")->fetchAll();
if (count($invalidPT) > 0) {
    foreach ($invalidPT as $pt) {
        $fkErrors[] = "Phiếu thuê #{$pt['MaPhieuThue']} tham chiếu đến phòng không tồn tại";
    }
}

if (count($fkErrors) == 0) {
    echo "<p class='success'>✅ Tất cả ràng buộc dữ liệu hợp lệ</p>";
} else {
    echo "<p class='error'>❌ Phát hiện " . count($fkErrors) . " lỗi ràng buộc:</p>";
    echo "<ul>";
    foreach ($fkErrors as $err) {
        echo "<li class='error'>$err</li>";
    }
    echo "</ul>";
}

echo "</div>";

// 8. Kiểm tra users
echo "<div class='section'>";
echo "<h2>👤 8. Người Dùng (NGUOIDUNG)</h2>";
$users = $db->query("SELECT MaNguoiDung, TenDangNhap, HoTen, VaiTro, TrangThai FROM NGUOIDUNG")->fetchAll();
echo "<table>";
echo "<tr><th>Mã</th><th>Tên Đăng Nhập</th><th>Họ Tên</th><th>Vai Trò</th><th>Trạng Thái</th></tr>";
foreach ($users as $u) {
    $badge = $u['TrangThai'] == 'Hoạt động' ? 'badge-success' : 'badge-danger';
    echo "<tr>";
    echo "<td>{$u['MaNguoiDung']}</td>";
    echo "<td><strong>{$u['TenDangNhap']}</strong></td>";
    echo "<td>{$u['HoTen']}</td>";
    echo "<td>{$u['VaiTro']}</td>";
    echo "<td><span class='badge $badge'>{$u['TrangThai']}</span></td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Kết luận
echo "<div class='section' style='background: #d4edda; border-color: #28a745;'>";
echo "<h2 class='success'>✅ Kết Luận</h2>";
echo "<p>Database đang hoạt động bình thường với:</p>";
echo "<ul>";
echo "<li>✅ " . count($tables) . " bảng cơ sở dữ liệu</li>";
echo "<li>✅ " . count($loaiPhongs) . " loại phòng</li>";
echo "<li>✅ " . count($phongs) . " phòng (Trống: $phongTrong, Đã thuê: $phongDaThue)</li>";
echo "<li>✅ " . count($phieuThues) . " phiếu thuê</li>";
echo "<li>✅ " . count($hoaDons) . " hóa đơn</li>";
echo "<li>✅ " . count($users) . " người dùng</li>";
if (count($errors) > 0) {
    echo "<li>⚠️ " . count($errors) . " lỗi đồng bộ cần khắc phục</li>";
}
if (count($fkErrors) > 0) {
    echo "<li>⚠️ " . count($fkErrors) . " lỗi ràng buộc cần khắc phục</li>";
}
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 30px; text-align: center;'>";
echo "<a href='index.php' style='padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 0 10px;'>🏠 Trang Chủ</a>";
echo "<a href='admin/index.php' style='padding: 15px 30px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; margin: 0 10px;'>⚙️ Admin</a>";
echo "<a href='test_database.php' style='padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; margin: 0 10px;'>🔍 Test DB</a>";
echo "</div>";

echo "</div></body></html>";
?>
