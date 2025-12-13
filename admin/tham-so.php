<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhongController.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$controller = new PhongController();
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Xử lý cập nhật tham số
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Cập nhật tham số
        $database->updateThamSo('SO_KHACH_TOI_DA', $_POST['SO_KHACH_TOI_DA']);
        $database->updateThamSo('TL_PHU_THU_KHACH_3', $_POST['TL_PHU_THU_KHACH_3'] / 100);
        $database->updateThamSo('HS_KHACH_NUOC_NGOAI', $_POST['HS_KHACH_NUOC_NGOAI']);
        
        // Cập nhật đơn giá loại phòng
        if (isset($_POST['donGiaA'])) {
            $stmt = $db->prepare("UPDATE LOAIPHONG SET DonGiaCoBan = ? WHERE TenLoai = 'Loại A'");
            $stmt->execute([$_POST['donGiaA']]);
        }
        if (isset($_POST['donGiaB'])) {
            $stmt = $db->prepare("UPDATE LOAIPHONG SET DonGiaCoBan = ? WHERE TenLoai = 'Loại B'");
            $stmt->execute([$_POST['donGiaB']]);
        }
        if (isset($_POST['donGiaC'])) {
            $stmt = $db->prepare("UPDATE LOAIPHONG SET DonGiaCoBan = ? WHERE TenLoai = 'Loại C'");
            $stmt->execute([$_POST['donGiaC']]);
        }
        
        $message = "✅ Cập nhật tham số thành công!";
    } catch (Exception $e) {
        $error = "❌ Lỗi: " . $e->getMessage();
    }
}

$thamSos = $database->getAllThamSo();
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG")->fetchAll();
$page_title = 'Tham Số Hệ Thống';
$phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tham Số</title>
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
                    <h2 class="section-title">🔧 Cấu Hình Quy Định</h2>
                </div>
                
                <form method="POST" style="max-width: 800px;">
                    <h3 style="color: #667eea; margin-top: 30px;">QĐ1: Đơn Giá Loại Phòng</h3>
                    <?php foreach ($loaiPhongs as $loai): ?>
                    <div class="form-group">
                        <label><?= htmlspecialchars($loai['TenLoai']) ?>:</label>
                        <input type="number" name="donGia<?= substr($loai['TenLoai'], -1) ?>" 
                               value="<?= $loai['DonGiaCoBan'] ?>" step="1000" required class="form-control">
                    </div>
                    <?php endforeach; ?>

                    <h3 style="color: #667eea; margin-top: 30px;">QĐ2: Số Khách Tối Đa</h3>
                    <div class="form-group">
                        <label>Số khách tối đa/phòng:</label>
                        <input type="number" name="SO_KHACH_TOI_DA" 
                               value="<?= $database->getThamSo('SO_KHACH_TOI_DA') ?>" 
                               min="1" max="5" required class="form-control">
                    </div>

                    <h3 style="color: #667eea; margin-top: 30px;">QĐ4: Phụ Thu & Hệ Số</h3>
                    <div class="form-group">
                        <label>Tỉ lệ phụ thu khách thứ 3 (%):</label>
                        <input type="number" name="TL_PHU_THU_KHACH_3" 
                               value="<?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>" 
                               step="1" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Hệ số khách nước ngoài:</label>
                        <input type="number" name="HS_KHACH_NUOC_NGOAI" 
                               value="<?= $database->getThamSo('HS_KHACH_NUOC_NGOAI') ?>" 
                               step="0.1" required class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Cập Nhật Tham Số</button>
                </form>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">📋 Bảng Tham Số Hiện Tại</h2>
                </div>
                
                <table class="data-table">
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
                            <td><strong><?= htmlspecialchars($ts['TenThamSo']) ?></strong></td>
                            <td><?= htmlspecialchars($ts['GiaTri']) ?></td>
                            <td><?= htmlspecialchars($ts['MoTa']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
