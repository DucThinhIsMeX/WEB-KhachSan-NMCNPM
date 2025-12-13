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
$page_title = 'Quản lý Phòng';
$phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <main class="main-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">➕ Thêm Phòng Mới</h2>
                </div>
                <form method="POST" style="max-width: 800px;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Số Phòng:</label>
                        <input type="text" name="soPhong" required placeholder="VD: 101" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Loại Phòng:</label>
                        <select name="maLoaiPhong" required class="form-control">
                            <?php foreach ($loaiPhongs as $loai): ?>
                                <option value="<?= $loai['MaLoaiPhong'] ?>">
                                    <?= $loai['TenLoai'] ?> - <?= number_format($loai['DonGiaCoBan']) ?>đ
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ghi Chú:</label>
                        <textarea name="ghiChu" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Lưu Phòng</button>
                </form>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">📋 Danh Sách Phòng</h2>
                </div>
                
                <table class="data-table">
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
                            <td><strong>#<?= $phong['MaPhong'] ?></strong></td>
                            <td><strong><?= htmlspecialchars($phong['SoPhong']) ?></strong></td>
                            <td><?= htmlspecialchars($phong['TenLoai']) ?></td>
                            <td><?= number_format($phong['DonGiaCoBan']) ?>đ</td>
                            <td>
                                <span class="status-badge <?= $phong['TinhTrang'] === 'Trống' ? 'available' : 'occupied' ?>">
                                    <?= $phong['TinhTrang'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($phong['GhiChu'] ?? '-') ?></td>
                            <td>
                                <?php if ($phong['TinhTrang'] === 'Trống'): ?>
                                    <a href="?action=delete&id=<?= $phong['MaPhong'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Xác nhận xóa phòng?')">🗑️ Xóa</a>
                                <?php else: ?>
                                    <span style="color: #999;">Đang thuê</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
