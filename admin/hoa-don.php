<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/HoaDonController.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$hoaDonCtrl = new HoaDonController();
$phieuThueCtrl = new PhieuThueController();

$message = '';
$error = '';

// Xử lý lập hóa đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $maHoaDon = $hoaDonCtrl->lapHoaDon(
            $_POST['maPhieuThue'],
            $_POST['tenKH'],
            $_POST['diaChi'],
            $_POST['ngayThanhToan']
        );
        $message = "Lập hóa đơn #$maHoaDon thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

$phieuThuesDangThue = $phieuThueCtrl->getPhieuThue('Đang thuê');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Hóa Đơn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💰 Quản lý Hóa Đơn</h1>
            <nav>
                <a href="index.php">🏠 Dashboard</a>
                <a href="phong.php">🛏️ Quản lý Phòng</a>
                <a href="phieu-thue.php">📝 Phiếu Thuê</a>
                <a href="hoa-don.php" class="active">💰 Hóa Đơn</a>
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
                <h2>➕ Lập Hóa Đơn Thanh Toán</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Chọn Phiếu Thuê:</label>
                        <select name="maPhieuThue" required>
                            <option value="">-- Chọn phiếu thuê --</option>
                            <?php foreach ($phieuThuesDangThue as $pt): 
                                $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                            ?>
                                <option value="<?= $pt['MaPhieuThue'] ?>">
                                    PT#<?= $pt['MaPhieuThue'] ?> - Phòng <?= $pt['SoPhong'] ?> 
                                    (<?= count($khachs) ?> khách, Từ <?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Khách Hàng/Cơ Quan:</label>
                        <input type="text" name="tenKH" required>
                    </div>

                    <div class="form-group">
                        <label>Địa Chỉ Thanh Toán:</label>
                        <input type="text" name="diaChi">
                    </div>

                    <div class="form-group">
                        <label>Ngày Thanh Toán:</label>
                        <input type="date" name="ngayThanhToan" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <button type="submit" class="btn">💳 Lập Hóa Đơn</button>
                </form>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Hệ thống Quản lý Khách sạn</p>
        </footer>
    </div>
</body>
</html>
