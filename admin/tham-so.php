<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Xử lý cập nhật tham số
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'action') {
                $database->updateThamSo($key, $value);
            }
        }
        
        // Cập nhật đơn giá loại phòng
        if (isset($_POST['donGiaA'])) {
            $db->exec("UPDATE LOAIPHONG SET DonGiaCoBan = {$_POST['donGiaA']} WHERE TenLoai = 'Loại A'");
        }
        if (isset($_POST['donGiaB'])) {
            $db->exec("UPDATE LOAIPHONG SET DonGiaCoBan = {$_POST['donGiaB']} WHERE TenLoai = 'Loại B'");
        }
        if (isset($_POST['donGiaC'])) {
            $db->exec("UPDATE LOAIPHONG SET DonGiaCoBan = {$_POST['donGiaC']} WHERE TenLoai = 'Loại C'");
        }
        
        $message = "Cập nhật tham số thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

$thamSos = $database->getAllThamSo();
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tham Số</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ Quản lý Tham Số Hệ Thống</h1>
            <nav>
                <a href="index.php">🏠 Dashboard</a>
                <a href="phong.php">🛏️ Quản lý Phòng</a>
                <a href="phieu-thue.php">📝 Phiếu Thuê</a>
                <a href="hoa-don.php">💰 Hóa Đơn</a>
                <a href="bao-cao.php">📊 Báo Cáo</a>
                <a href="tham-so.php" class="active">⚙️ Tham Số</a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <section>
                <h2>🔧 Cấu Hình Quy Định</h2>
                <form method="POST">
                    <h3>QĐ1: Đơn Giá Loại Phòng</h3>
                    <?php foreach ($loaiPhongs as $loai): ?>
                    <div class="form-group">
                        <label><?= $loai['TenLoai'] ?>:</label>
                        <input type="number" name="donGia<?= substr($loai['TenLoai'], -1) ?>" 
                               value="<?= $loai['DonGiaCoBan'] ?>" step="1000" required>
                    </div>
                    <?php endforeach; ?>

                    <h3>QĐ2: Số Khách Tối Đa</h3>
                    <div class="form-group">
                        <label>Số khách tối đa/phòng:</label>
                        <input type="number" name="SO_KHACH_TOI_DA" 
                               value="<?= $database->getThamSo('SO_KHACH_TOI_DA') ?>" min="1" max="5" required>
                    </div>

                    <h3>QĐ4: Phụ Thu & Hệ Số</h3>
                    <div class="form-group">
                        <label>Tỉ lệ phụ thu khách thứ 3 (%):</label>
                        <input type="number" name="TL_PHU_THU_KHACH_3" 
                               value="<?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>" 
                               step="1" required>
                    </div>
                    <div class="form-group">
                        <label>Hệ số khách nước ngoài:</label>
                        <input type="number" name="HS_KHACH_NUOC_NGOAI" 
                               value="<?= $database->getThamSo('HS_KHACH_NUOC_NGOAI') ?>" 
                               step="0.1" required>
                    </div>

                    <button type="submit" class="btn">💾 Cập Nhật Tham Số</button>
                </form>
            </section>

            <section>
                <h2>📋 Bảng Tham Số Hiện Tại</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tên Tham Số</th>
                            <th>Giá Trị</th>
                            <th>Mô Tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($thamSos as $ts): ?>
                        <tr>
                            <td><strong><?= $ts['TenThamSo'] ?></strong></td>
                            <td><?= $ts['GiaTri'] ?></td>
                            <td><?= $ts['MoTa'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Hệ thống Quản lý Khách sạn</p>
        </footer>
    </div>
</body>
</html>
