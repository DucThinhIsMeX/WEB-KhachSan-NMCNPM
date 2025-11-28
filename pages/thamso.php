<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();
$message = '';

// Xử lý cập nhật tham số
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if ($key != 'submit') {
                $database->updateThamSo($key, $value);
            }
        }
        $message = '<div class="alert alert-success">Cập nhật tham số thành công!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">Lỗi: ' . $e->getMessage() . '</div>';
    }
}

$thamSos = $db->query("SELECT * FROM THAMSO")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tham số</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ Quản lý Tham số Hệ thống</h1>
            <nav>
                <a href="../index.php">Trang chủ</a>
                <a href="phong.php">Quản lý Phòng</a>
                <a href="khachhang.php">Khách hàng</a>
                <a href="phieuthue.php">Phiếu thuê</a>
                <a href="hoadon.php">Hóa đơn</a>
                <a href="baocao.php">Báo cáo</a>
                <a href="thamso.php">Tham số</a>
            </nav>
        </header>

        <main>
            <?= $message ?>
            
            <h2>Cấu hình Tham số (QĐ6)</h2>
            <form method="POST">
                <?php foreach ($thamSos as $ts): ?>
                <div class="form-group">
                    <label><?= $ts['TenThamSo'] ?>:</label>
                    <input type="number" step="0.01" name="<?= $ts['TenThamSo'] ?>" value="<?= $ts['GiaTri'] ?>" required>
                </div>
                <?php endforeach; ?>
                <button type="submit" name="submit" class="btn">Cập nhật</button>
            </form>
        </main>
    </div>
</body>
</html>
