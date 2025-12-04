<?php
require_once __DIR__ . '/../controllers/PhongController.php';

$controller = new PhongController();
$message = '';

// Xử lý thêm phòng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_phong'])) {
    try {
        $controller->themPhong($_POST['soPhong'], $_POST['maLoaiPhong'], $_POST['ghiChu']);
        $message = '<div class="alert alert-success">Thêm phòng thành công!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">Lỗi: ' . $e->getMessage() . '</div>';
    }
}

$database = new Database();
$db = $database->connect();
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG")->fetchAll();
$phongs = $controller->getAllPhong();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏨 Quản lý Phòng <span style="background: #dc3545; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.7em;">ADMIN</span></h1>
            <nav>
                <a href="http://localhost:8000">Dashboard</a>
                <a href="phong.php">Quản lý Phòng</a>
                <a href="khachhang.php">Khách hàng</a>
                <a href="phieuthue.php">Phiếu thuê</a>
                <a href="hoadon.php">Hóa đơn</a>
                <a href="baocao.php">Báo cáo</a>
                <a href="thamso.php">Tham số</a>
                <a href="http://localhost:5500" target="_blank" style="background: #28a745;">🌐 Trang khách</a>
            </nav>
        </header>

        <main>
            <?= $message ?>
            
            <h2>Thêm Phòng Mới</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Số phòng:</label>
                    <input type="text" name="soPhong" required>
                </div>
                <div class="form-group">
                    <label>Loại phòng:</label>
                    <select name="maLoaiPhong" required>
                        <?php foreach ($loaiPhongs as $loai): ?>
                        <option value="<?= $loai['MaLoaiPhong'] ?>"><?= $loai['TenLoai'] ?> - <?= number_format($loai['DonGiaCoBan']) ?>đ</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ghi chú:</label>
                    <textarea name="ghiChu" rows="3"></textarea>
                </div>
                <button type="submit" name="them_phong" class="btn">Thêm Phòng</button>
            </form>

            <h2>Danh sách Phòng</h2>
            <table>
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Số phòng</th>
                        <th>Loại</th>
                        <th>Đơn giá</th>
                        <th>Tình trạng</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($phongs as $phong): ?>
                    <tr>
                        <td><?= $phong['MaPhong'] ?></td>
                        <td><?= $phong['SoPhong'] ?></td>
                        <td><?= $phong['TenLoai'] ?></td>
                        <td><?= number_format($phong['DonGiaCoBan']) ?>đ</td>
                        <td><span class="status-<?= strtolower(str_replace(' ', '-', $phong['TinhTrang'])) ?>"><?= $phong['TinhTrang'] ?></span></td>
                        <td><?= $phong['GhiChu'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
