<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhongController.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$controller = new PhongController();
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Xử lý thêm phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $controller->themPhong($_POST['soPhong'], $_POST['maLoaiPhong'], $_POST['ghiChu']);
        $message = "Thêm phòng thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa phòng
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $controller->xoaPhong($_GET['id']);
        $message = "Xóa phòng thành công!";
    } catch (Exception $e) {
        $error = "Không thể xóa phòng đang thuê!";
    }
}

// Lấy danh sách loại phòng
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG")->fetchAll();
$phongs = $controller->getAllPhong();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🛏️ Quản lý Phòng</h1>
            <nav>
                <a href="index.php">🏠 Dashboard</a>
                <a href="phong.php" class="active">🛏️ Quản lý Phòng</a>
                <a href="phieu-thue.php">📝 Phiếu Thuê</a>
                <a href="hoa-don.php">💰 Hóa Đơn</a>
                <a href="bao-cao.php">📊 Báo Cáo</a>
                <a href="tham-so.php">⚙️ Tham Số</a>
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
                <h2>➕ Thêm Phòng Mới</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Số Phòng:</label>
                        <input type="text" name="soPhong" required placeholder="VD: 101">
                    </div>
                    <div class="form-group">
                        <label>Loại Phòng:</label>
                        <select name="maLoaiPhong" required>
                            <?php foreach ($loaiPhongs as $loai): ?>
                                <option value="<?= $loai['MaLoaiPhong'] ?>">
                                    <?= $loai['TenLoai'] ?> - <?= number_format($loai['DonGiaCoBan']) ?>đ
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ghi Chú:</label>
                        <textarea name="ghiChu" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn">💾 Lưu Phòng</button>
                </form>
            </section>

            <section>
                <h2>📋 Danh Sách Phòng</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Số Phòng</th>
                            <th>Loại</th>
                            <th>Đơn Giá</th>
                            <th>Tình Trạng</th>
                            <th>Ghi Chú</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($phongs as $phong): ?>
                        <tr>
                            <td><?= $phong['MaPhong'] ?></td>
                            <td><strong><?= $phong['SoPhong'] ?></strong></td>
                            <td><?= $phong['TenLoai'] ?></td>
                            <td><?= number_format($phong['DonGiaCoBan']) ?>đ</td>
                            <td><span class="status-<?= strtolower(str_replace(' ', '-', $phong['TinhTrang'])) ?>">
                                <?= $phong['TinhTrang'] ?>
                            </span></td>
                            <td><?= $phong['GhiChu'] ?? '-' ?></td>
                            <td>
                                <?php if ($phong['TinhTrang'] === 'Trống'): ?>
                                    <a href="?action=delete&id=<?= $phong['MaPhong'] ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Xác nhận xóa phòng?')">🗑️ Xóa</a>
                                <?php else: ?>
                                    <span style="color: #999;">Đang thuê</span>
                                <?php endif; ?>
                            </td>
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
