<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Debug Database</title>
    <link rel='stylesheet' href='assets/css/style.css'>
</head>
<body>
<div class='container' style='padding: 30px;'>";

echo "<h1>🔧 Debug Database</h1>";

// Kiểm tra file database
$db_file = __DIR__ . '/database/hotel.db';
echo "<h3>1. Kiểm tra File Database</h3>";
if (file_exists($db_file)) {
    echo "✓ File tồn tại: $db_file<br>";
    echo "✓ Kích thước: " . filesize($db_file) . " bytes<br>";
    echo "✓ Quyền: " . substr(sprintf('%o', fileperms($db_file)), -4) . "<br>";
} else {
    echo "✗ File KHÔNG tồn tại: $db_file<br>";
    echo "<strong>Hãy chạy: php database/init.php</strong><br>";
}

// Kiểm tra PDO SQLite
echo "<h3>2. Kiểm tra PDO SQLite</h3>";
$drivers = PDO::getAvailableDrivers();
if (in_array('sqlite', $drivers)) {
    echo "✓ PDO SQLite driver có sẵn<br>";
    echo "✓ Danh sách drivers: " . implode(', ', $drivers) . "<br>";
} else {
    echo "✗ PDO SQLite driver KHÔNG có sẵn<br>";
}

// Kiểm tra kết nối
echo "<h3>3. Kiểm tra Kết nối</h3>";
try {
    $database = new Database();
    $db = $database->connect();
    echo "✓ Kết nối database thành công<br>";
    
    // Kiểm tra Foreign Keys
    $fk = $db->query("PRAGMA foreign_keys")->fetch();
    echo "✓ Foreign Keys: " . ($fk['foreign_keys'] ? 'Enabled' : 'Disabled') . "<br>";
    
    // Kiểm tra Journal Mode
    $jm = $db->query("PRAGMA journal_mode")->fetch();
    echo "✓ Journal Mode: " . $jm['journal_mode'] . "<br>";
    
} catch(PDOException $e) {
    echo "✗ Lỗi kết nối: " . $e->getMessage() . "<br>";
}

// Kiểm tra các bảng
echo "<h3>4. Kiểm tra Các Bảng</h3>";
try {
    $tables = ['LOAIPHONG', 'PHONG', 'KHACHHANG', 'PHIEUTHUE', 'CHITIET_THUE', 
               'HOADON', 'CHITIET_HOADON', 'BAOCAO_DOANHTHU', 'CHITIET_BAOCAO', 'THAMSO'];
    
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "✓ $table: $count bản ghi<br>";
    }
} catch(PDOException $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "<br>";
}

// Kiểm tra schema
echo "<h3>5. Schema Các Bảng</h3>";
try {
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "<h4>$table</h4>";
        echo "<pre>";
        $schema = $db->query("SELECT sql FROM sqlite_master WHERE name='$table'")->fetch();
        echo htmlspecialchars($schema['sql']);
        echo "</pre>";
    }
} catch(PDOException $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "<br>";
}

// Test query mẫu
echo "<h3>6. Test Query Mẫu</h3>";
try {
    echo "<h4>Phòng với loại phòng:</h4>";
    $stmt = $db->query("SELECT P.SoPhong, L.TenLoai, L.DonGiaCoBan, P.TinhTrang 
                        FROM PHONG P 
                        JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                        LIMIT 5");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Số phòng</th><th>Loại</th><th>Giá</th><th>Tình trạng</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['SoPhong']}</td><td>{$row['TenLoai']}</td><td>" . number_format($row['DonGiaCoBan']) . "</td><td>{$row['TinhTrang']}</td></tr>";
    }
    echo "</table>";
} catch(PDOException $e) {
    echo "✗ Lỗi query: " . $e->getMessage() . "<br>";
}

echo "<h3 style='color: green;'>Hoàn tất kiểm tra!</h3>";
echo "<p><a href='test_database.php' class='btn'>Xem báo cáo chi tiết</a> ";
echo "<a href='index.php' class='btn'>Về trang chủ</a></p>";

echo "</div></body></html>";
?>
