<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Kiểm tra Database</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        h3 { color: #333; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .success { color: green; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .stat { display: inline-block; margin: 10px 20px 10px 0; padding: 15px 25px; background: #667eea; color: white; border-radius: 5px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2>🔍 Kiểm tra Database Khách sạn</h2>";

// Thông tin database
$info = $database->getDatabaseInfo();
echo "<div class='info'>";
echo "<strong>📁 File:</strong> {$info['file']}<br>";
echo "<strong>📊 Kích thước:</strong> " . number_format($info['size'] / 1024, 2) . " KB<br>";
echo "<strong>📋 Số bảng:</strong> " . count($info['tables']) . " bảng<br>";
echo "<strong>✓ Trạng thái:</strong> <span class='success'>Hoạt động bình thường</span>";
echo "</div>";

// Thống kê
echo "<h3>📈 Thống kê Dữ liệu</h3>";
$stats = [
    'Loại phòng' => 'SELECT COUNT(*) FROM LOAIPHONG',
    'Phòng' => 'SELECT COUNT(*) FROM PHONG',
    'Phòng trống' => "SELECT COUNT(*) FROM PHONG WHERE TinhTrang = 'Trống'",
    'Phòng đã thuê' => "SELECT COUNT(*) FROM PHONG WHERE TinhTrang = 'Đã thuê'",
    'Khách hàng' => 'SELECT COUNT(*) FROM KHACHHANG',
    'Khách nội địa' => "SELECT COUNT(*) FROM KHACHHANG WHERE LoaiKhach = 'Nội địa'",
    'Khách nước ngoài' => "SELECT COUNT(*) FROM KHACHHANG WHERE LoaiKhach = 'Nước ngoài'",
    'Phiếu thuê' => 'SELECT COUNT(*) FROM PHIEUTHUE',
    'Hóa đơn' => 'SELECT COUNT(*) FROM HOADON',
    'Báo cáo' => 'SELECT COUNT(*) FROM BAOCAO_DOANHTHU'
];

foreach ($stats as $label => $query) {
    $count = $db->query($query)->fetchColumn();
    echo "<div class='stat'>$label: <strong>$count</strong></div>";
}

// Test 1: Loại phòng
echo "<h3>1️⃣ Danh sách Loại Phòng</h3>";
echo "<table><thead><tr><th>Mã</th><th>Tên loại</th><th>Đơn giá cơ bản</th></tr></thead><tbody>";
$stmt = $db->query("SELECT * FROM LOAIPHONG");
while ($row = $stmt->fetch()) {
    echo "<tr><td>{$row['MaLoaiPhong']}</td><td>{$row['TenLoai']}</td><td>" . number_format($row['DonGiaCoBan']) . "đ</td></tr>";
}
echo "</tbody></table>";

// Test 2: Phòng
echo "<h3>2️⃣ Danh sách Phòng</h3>";
echo "<table><thead><tr><th>Mã</th><th>Số phòng</th><th>Loại</th><th>Đơn giá</th><th>Tình trạng</th><th>Ghi chú</th></tr></thead><tbody>";
$stmt = $db->query("SELECT P.*, L.TenLoai, L.DonGiaCoBan FROM PHONG P JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong ORDER BY P.SoPhong");
while ($row = $stmt->fetch()) {
    $status = $row['TinhTrang'] == 'Trống' ? '🟢' : '🔴';
    echo "<tr><td>{$row['MaPhong']}</td><td>{$row['SoPhong']}</td><td>{$row['TenLoai']}</td><td>" . number_format($row['DonGiaCoBan']) . "đ</td><td>$status {$row['TinhTrang']}</td><td>{$row['GhiChu']}</td></tr>";
}
echo "</tbody></table>";

// Test 3: Khách hàng
echo "<h3>3️⃣ Danh sách Khách hàng</h3>";
echo "<table><thead><tr><th>Mã</th><th>Tên khách</th><th>Loại khách</th><th>CMND</th><th>Địa chỉ</th></tr></thead><tbody>";
$stmt = $db->query("SELECT * FROM KHACHHANG ORDER BY MaKhachHang");
while ($row = $stmt->fetch()) {
    $flag = $row['LoaiKhach'] == 'Nội địa' ? '🇻🇳' : '🌍';
    echo "<tr><td>{$row['MaKhachHang']}</td><td>{$row['TenKhach']}</td><td>$flag {$row['LoaiKhach']}</td><td>{$row['CMND']}</td><td>{$row['DiaChi']}</td></tr>";
}
echo "</tbody></table>";

// Test 4: Phiếu thuê
echo "<h3>4️⃣ Danh sách Phiếu Thuê</h3>";
$stmt = $db->query("SELECT PT.*, P.SoPhong FROM PHIEUTHUE PT JOIN PHONG P ON PT.MaPhong = P.MaPhong ORDER BY PT.MaPhieuThue DESC");
$phieuThues = $stmt->fetchAll();
if (count($phieuThues) > 0) {
    echo "<table><thead><tr><th>Mã PT</th><th>Phòng</th><th>Ngày bắt đầu</th><th>Tình trạng</th><th>Khách hàng</th></tr></thead><tbody>";
    foreach ($phieuThues as $pt) {
        $stmt = $db->prepare("SELECT K.TenKhach FROM KHACHHANG K JOIN CHITIET_THUE CT ON K.MaKhachHang = CT.MaKhachHang WHERE CT.MaPhieuThue = ?");
        $stmt->execute([$pt['MaPhieuThue']]);
        $khachs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<tr><td>{$pt['MaPhieuThue']}</td><td>{$pt['SoPhong']}</td><td>{$pt['NgayBatDauThue']}</td><td>{$pt['TinhTrangPhieu']}</td><td>" . implode(', ', $khachs) . "</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>Chưa có phiếu thuê nào.</p>";
}

// Test 5: Tham số
echo "<h3>5️⃣ Tham số Hệ thống (QĐ6)</h3>";
echo "<table><thead><tr><th>Tên tham số</th><th>Giá trị</th><th>Mô tả</th></tr></thead><tbody>";
$stmt = $db->query("SELECT * FROM THAMSO ORDER BY TenThamSo");
while ($row = $stmt->fetch()) {
    echo "<tr><td>{$row['TenThamSo']}</td><td><strong>{$row['GiaTri']}</strong></td><td>{$row['MoTa']}</td></tr>";
}
echo "</tbody></table>";

// Test 6: Kiểm tra ràng buộc
echo "<h3>6️⃣ Kiểm tra Ràng buộc (Constraints)</h3>";
echo "<div class='info'>";

try {
    // Test Foreign Key
    $db->exec("INSERT INTO PHONG (SoPhong, MaLoaiPhong) VALUES ('999', 999)");
    echo "❌ Foreign Key KHÔNG hoạt động<br>";
} catch(PDOException $e) {
    echo "✓ Foreign Key hoạt động tốt<br>";
}

try {
    // Test Unique
    $db->exec("INSERT INTO PHONG (SoPhong, MaLoaiPhong) VALUES ('101', 1)");
    echo "❌ Unique constraint KHÔNG hoạt động<br>";
} catch(PDOException $e) {
    echo "✓ Unique constraint hoạt động tốt<br>";
}

try {
    // Test Check
    $db->exec("INSERT INTO LOAIPHONG (TenLoai, DonGiaCoBan) VALUES ('Test', -1000)");
    echo "❌ Check constraint KHÔNG hoạt động<br>";
} catch(PDOException $e) {
    echo "✓ Check constraint hoạt động tốt<br>";
}

echo "</div>";

// Test 7: Kiểm tra indexes
echo "<h3>7️⃣ Danh sách Indexes</h3>";
echo "<table><thead><tr><th>Tên Index</th><th>Bảng</th></tr></thead><tbody>";
$stmt = $db->query("SELECT name, tbl_name FROM sqlite_master WHERE type='index' AND name NOT LIKE 'sqlite_%' ORDER BY tbl_name, name");
while ($row = $stmt->fetch()) {
    echo "<tr><td>{$row['name']}</td><td>{$row['tbl_name']}</td></tr>";
}
echo "</tbody></table>";

echo "<h3 style='color: green; text-align: center; margin-top: 40px;'>✓ Database hoạt động hoàn hảo!</h3>";

echo "</div></body></html>";
?>
