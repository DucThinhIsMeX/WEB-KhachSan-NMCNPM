<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Kiểm Tra Database</title>
    <link rel='stylesheet' href='assets/css/style.css'>
</head>
<body>
<div class='container'>
    <header>
        <h1>🔍 Kiểm Tra Database</h1>
    </header>
    <main>";

// Thông tin database
$info = $database->getDatabaseInfo();
echo "<section>
    <h2>📊 Thông Tin Database</h2>
    <p><strong>File:</strong> {$info['file']}</p>
    <p><strong>Kích thước:</strong> " . number_format($info['size']/1024, 2) . " KB</p>
    <p><strong>Số bảng:</strong> " . count($info['tables']) . "</p>
</section>";

// Danh sách bảng
echo "<section>
    <h2>📋 Danh Sách Bảng</h2>
    <table>
        <thead>
            <tr><th>Tên Bảng</th><th>Số Bản Ghi</th></tr>
        </thead>
        <tbody>";

foreach ($info['tables'] as $table) {
    $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "<tr><td><strong>$table</strong></td><td>$count</td></tr>";
}

echo "</tbody></table></section>";

// Tham số hệ thống
echo "<section>
    <h2>⚙️ Tham Số Hệ Thống</h2>
    <table>
        <thead>
            <tr><th>Tên</th><th>Giá Trị</th><th>Mô Tả</th></tr>
        </thead>
        <tbody>";

$thamsos = $database->getAllThamSo();
foreach ($thamsos as $ts) {
    echo "<tr>
        <td><strong>{$ts['TenThamSo']}</strong></td>
        <td>{$ts['GiaTri']}</td>
        <td>{$ts['MoTa']}</td>
    </tr>";
}

echo "</tbody></table></section>";

// Các nút
echo "<section style='text-align: center; margin: 30px 0;'>
    <a href='index.php' class='btn'>🏠 Trang Chủ</a>
    <a href='admin/index.php' class='btn'>🔐 Admin</a>
    <a href='database/reset.php' class='btn btn-danger'>🔄 Reset Database</a>
</section>";

echo "</main>
    <footer>
        <p>&copy; 2024 Hệ thống Quản lý Khách sạn</p>
    </footer>
</div>
</body>
</html>";
?>
