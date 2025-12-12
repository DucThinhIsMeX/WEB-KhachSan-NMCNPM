<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../controllers/KhachHangController.php';
require_once __DIR__ . '/../controllers/PhongController.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$phieuThueCtrl = new PhieuThueController();
$khachHangCtrl = new KhachHangController();
$phongCtrl = new PhongController();
$database = new Database();

$message = '';
$error = '';

// Xử lý tạo phiếu thuê
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        // Thêm khách hàng
        $danhSachKhach = [];
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_POST["tenKhach$i"])) {
                $maKhach = $khachHangCtrl->themKhachHang(
                    $_POST["tenKhach$i"],
                    $_POST["loaiKhach$i"],
                    $_POST["cmnd$i"],
                    $_POST["diaChi$i"]
                );
                $danhSachKhach[] = $maKhach;
            }
        }
        
        // Tạo phiếu thuê
        $maPhieuThue = $phieuThueCtrl->taoPhieuThue(
            $_POST['maPhong'],
            $_POST['ngayBatDau'],
            $danhSachKhach
        );
        
        $message = "Tạo phiếu thuê #$maPhieuThue thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

$phongsTrong = $phongCtrl->traCuuPhong(null, 'Trống');
$phieuThues = $phieuThueCtrl->getPhieuThue();
$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phiếu Thuê</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .khach-group { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .khach-group h4 { margin-bottom: 10px; color: #667eea; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📝 Quản lý Phiếu Thuê</h1>
            <nav>
                <a href="index.php">🏠 Dashboard</a>
                <a href="phong.php">🛏️ Quản lý Phòng</a>
                <a href="phieu-thue.php" class="active">📝 Phiếu Thuê</a>
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
                <h2>➕ Tạo Phiếu Thuê Mới</h2>
                <form method="POST" id="formPhieuThue">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Chọn Phòng Trống:</label>
                        <select name="maPhong" required>
                            <option value="">-- Chọn phòng --</option>
                            <?php foreach ($phongsTrong as $phong): ?>
                                <option value="<?= $phong['MaPhong'] ?>">
                                    Phòng <?= $phong['SoPhong'] ?> - <?= $phong['TenLoai'] ?> 
                                    (<?= number_format($phong['DonGiaCoBan']) ?>đ)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ngày Bắt Đầu Thuê:</label>
                        <input type="date" name="ngayBatDau" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <h3>Thông Tin Khách Hàng (Tối đa <?= $soKhachToiDa ?> khách)</h3>

                    <?php for ($i = 1; $i <= $soKhachToiDa; $i++): ?>
                    <div class="khach-group">
                        <h4>👤 Khách <?= $i ?> <?= $i == 1 ? '(Bắt buộc)' : '(Tùy chọn)' ?></h4>
                        <div class="form-group">
                            <label>Tên Khách:</label>
                            <input type="text" name="tenKhach<?= $i ?>" <?= $i == 1 ? 'required' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label>Loại Khách:</label>
                            <select name="loaiKhach<?= $i ?>">
                                <option value="Nội địa">Nội địa</option>
                                <option value="Nước ngoài">Nước ngoài</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>CMND/CCCD:</label>
                            <input type="text" name="cmnd<?= $i ?>">
                        </div>
                        <div class="form-group">
                            <label>Địa Chỉ:</label>
                            <input type="text" name="diaChi<?= $i ?>">
                        </div>
                    </div>
                    <?php endfor; ?>

                    <button type="submit" class="btn">💾 Lưu Phiếu Thuê</button>
                </form>
            </section>

            <section>
                <h2>📋 Danh Sách Phiếu Thuê</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mã PT</th>
                            <th>Số Phòng</th>
                            <th>Ngày Thuê</th>
                            <th>Tình Trạng</th>
                            <th>Chi Tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($phieuThues as $pt): 
                            $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                        ?>
                        <tr>
                            <td><strong><?= $pt['MaPhieuThue'] ?></strong></td>
                            <td>Phòng <?= $pt['SoPhong'] ?></td>
                            <td><?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?></td>
                            <td><span class="status-<?= strtolower(str_replace(' ', '-', $pt['TinhTrangPhieu'])) ?>">
                                <?= $pt['TinhTrangPhieu'] ?>
                            </span></td>
                            <td>
                                <?php foreach ($khachs as $k): ?>
                                    <div><?= $k['TenKhach'] ?> (<?= $k['LoaiKhach'] ?>)</div>
                                <?php endforeach; ?>
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
