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
    <script src='https://unpkg.com/@phosphor-icons/web'></script>
</head>
<body>
<div class='container'>
    <header>
        <h1><i class='ph ph-database'></i> Kiểm Tra Database</h1>
    </header>
    <main>";

// Thông tin database
$info = $database->getDatabaseInfo();
echo "<section>
    <h2><i class='ph ph-info'></i> Thông Tin Database</h2>
    <p><strong>File:</strong> {$info['file']}</p>
    <p><strong>Kích thước:</strong> " . number_format($info['size']/1024, 2) . " KB</p>
    <p><strong>Số bảng:</strong> " . count($info['tables']) . "</p>
</section>";

// Danh sách bảng
echo "<section>
    <h2><i class='ph ph-table'></i> Danh Sách Bảng</h2>
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
    <h2><i class='ph ph-gear'></i> Tham Số Hệ Thống</h2>
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
    <a href='index.php' class='btn'><i class='ph ph-house'></i> Trang Chủ</a>
    <a href='admin/index.php' class='btn'><i class='ph ph-lock-key'></i> Admin</a>
    <a href='database/reset.php' class='btn btn-danger'><i class='ph ph-arrow-clockwise'></i> Reset Database</a>
</section>";

echo "</main>
    <footer>
        <p>&copy; 2024 Hệ thống Quản lý Khách sạn</p>
    </footer>
</div>
</body>
</html>";
?>
