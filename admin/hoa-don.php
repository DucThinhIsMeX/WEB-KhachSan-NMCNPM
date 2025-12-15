<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/HoaDonController.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$hoaDonCtrl = new HoaDonController();
$phieuThueCtrl = new PhieuThueController();
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';
$previewData = null;

// Xử lý preview hóa đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'preview') {
    try {
        $maPhieuThue = $_POST['maPhieuThue'];
        
        // Lấy thông tin phiếu thuê
        $stmt = $db->prepare("SELECT PT.*, P.SoPhong, P.MaPhong, L.TenLoai, L.DonGiaCoBan 
                              FROM PHIEUTHUE PT
                              JOIN PHONG P ON PT.MaPhong = P.MaPhong
                              JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong
                              WHERE PT.MaPhieuThue = ?");
        $stmt->execute([$maPhieuThue]);
        $phieuThue = $stmt->fetch();
        
        if (!$phieuThue) {
            throw new Exception("Không tìm thấy phiếu thuê");
        }
        
        // Lấy danh sách khách
        $khachs = $phieuThueCtrl->getChiTietKhach($maPhieuThue);
        
        // Tính số ngày thuê
        $ngayBatDau = new DateTime($phieuThue['NgayBatDauThue']);
        $ngayThanhToan = new DateTime($_POST['ngayThanhToan']);
        $soNgay = $ngayThanhToan->diff($ngayBatDau)->days;
        if ($soNgay == 0) $soNgay = 1;
        
        // Tính toán theo QĐ4
        $donGiaCoBan = floatval($phieuThue['DonGiaCoBan']);
        $donGiaTinh = $donGiaCoBan;
        $soKhach = count($khachs);
        $coKhachNN = false;
        
        foreach ($khachs as $k) {
            if ($k['LoaiKhach'] === 'Nước ngoài') {
                $coKhachNN = true;
                break;
            }
        }
        
        // Phụ thu khách thứ 3
        $phuThuKhach3 = 0;
        $soKhachToiDa = intval($database->getThamSo('SO_KHACH_TOI_DA'));
        if ($soKhach >= $soKhachToiDa) {
            $tlPhuThu = floatval($database->getThamSo('TL_PHU_THU_KHACH_3'));
            $phuThuKhach3 = $donGiaCoBan * $tlPhuThu;
            $donGiaTinh += $phuThuKhach3;
        }
        
        // Hệ số khách nước ngoài
        $heSoNN = 0;
        if ($coKhachNN) {
            $hsNN = floatval($database->getThamSo('HS_KHACH_NUOC_NGOAI'));
            $donGiaTinh = $donGiaTinh * $hsNN;
            $heSoNN = $hsNN;
        }
        
        $thanhTien = $donGiaTinh * $soNgay;
        
        $previewData = [
            'phieuThue' => $phieuThue,
            'khachs' => $khachs,
            'soNgay' => $soNgay,
            'donGiaCoBan' => $donGiaCoBan,
            'phuThuKhach3' => $phuThuKhach3,
            'heSoNN' => $heSoNN,
            'donGiaTinh' => $donGiaTinh,
            'thanhTien' => $thanhTien,
            'tenKH' => $_POST['tenKH'],
            'diaChi' => $_POST['diaChi'],
            'ngayThanhToan' => $_POST['ngayThanhToan']
        ];
        
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý lập hóa đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
    try {
        $maHoaDon = $hoaDonCtrl->lapHoaDon(
            $_POST['maPhieuThue'],
            $_POST['tenKH'],
            $_POST['diaChi'],
            $_POST['ngayThanhToan']
        );
        $message = "✅ Lập hóa đơn #$maHoaDon thành công!";
    } catch (Exception $e) {
        $error = "❌ Lỗi: " . $e->getMessage();
    }
}

$phieuThuesDangThue = $phieuThueCtrl->getPhieuThue('Đang thuê');

// Lấy danh sách hóa đơn
$stmt = $db->query("SELECT H.*, P.SoPhong 
                    FROM HOADON H
                    JOIN PHIEUTHUE PT ON H.MaPhieuThue = PT.MaPhieuThue
                    JOIN PHONG P ON PT.MaPhong = P.MaPhong
                    ORDER BY H.NgayThanhToan DESC
                    LIMIT 10");
$hoaDons = $stmt->fetchAll();

$page_title = 'Hóa Đơn';
$phongDaThue = 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Hóa Đơn</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .preview-box {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .preview-box h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .calculation-step {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
            border-radius: 5px;
        }
        .calculation-step strong {
            color: #667eea;
        }
        .total-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2em;
            margin: 20px 0;
        }
        .guest-list {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .guest-item {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .guest-item:last-child {
            border-bottom: none;
        }
        .badge-foreign {
            background: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .badge-local {
            background: #51cf66;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <?php 
    $page_title = 'Hóa Đơn';
    include 'includes/sidebar.php'; 
    ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <main class="main-container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="ph ph-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="ph ph-currency-circle-dollar"></i> Lập Hóa Đơn Thanh Toán
                    </h2>
                </div>

                <form method="POST" style="max-width: 800px;">
                    <input type="hidden" name="action" value="preview">
                    
                    <div class="form-group">
                        <label><i class="ph ph-ticket"></i> Chọn Phiếu Thuê:</label>
                        <select name="maPhieuThue" required class="form-control">
                            <option value="">-- Chọn phiếu thuê --</option>
                            <?php foreach ($phieuThuesDangThue as $pt): 
                                $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                            ?>
                                <option value="<?= $pt['MaPhieuThue'] ?>" 
                                        <?= $previewData && $previewData['phieuThue']['MaPhieuThue'] == $pt['MaPhieuThue'] ? 'selected' : '' ?>>
                                    PT#<?= $pt['MaPhieuThue'] ?> - Phòng <?= htmlspecialchars($pt['SoPhong']) ?> 
                                    (<?= count($khachs) ?> khách, Từ <?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="ph ph-user"></i> Khách Hàng/Cơ Quan:</label>
                        <input type="text" name="tenKH" required class="form-control"
                               value="<?= htmlspecialchars($previewData['tenKH'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="ph ph-map-pin"></i> Địa Chỉ Thanh Toán:</label>
                        <input type="text" name="diaChi" class="form-control"
                               value="<?= htmlspecialchars($previewData['diaChi'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="ph ph-calendar"></i> Ngày Thanh Toán:</label>
                        <input type="date" name="ngayThanhToan" required class="form-control"
                               value="<?= $previewData['ngayThanhToan'] ?? date('Y-m-d') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-magnifying-glass"></i> Xem Chi Tiết & Tính Toán
                    </button>
                </form>

                <?php if ($previewData): ?>
                <div class="preview-box">
                    <h3><i class="ph ph-receipt"></i> Chi Tiết Hóa Đơn - PT#<?= $previewData['phieuThue']['MaPhieuThue'] ?></h3>
                    
                    <div class="guest-list">
                        <h4><i class="ph ph-users"></i> Danh Sách Khách (<?= count($previewData['khachs']) ?> người)</h4>
                        <?php foreach ($previewData['khachs'] as $k): ?>
                        <div class="guest-item">
                            <i class="ph ph-user"></i>
                            <strong><?= htmlspecialchars($k['TenKhach']) ?></strong>
                            <span class="badge-<?= $k['LoaiKhach'] === 'Nước ngoài' ? 'foreign' : 'local' ?>">
                                <?= htmlspecialchars($k['LoaiKhach']) ?>
                            </span>
                            <?php if ($k['CMND']): ?>
                                - <i class="ph ph-identification-card"></i> <?= htmlspecialchars($k['CMND']) ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <h4 style="margin-top: 20px;">
                        <i class="ph ph-calculator"></i> Công Thức Tính Toán (QĐ4)
                    </h4>
                    
                    <div class="calculation-step">
                        <strong>1. Đơn giá cơ bản:</strong><br>
                        Loại phòng: <?= htmlspecialchars($previewData['phieuThue']['TenLoai']) ?><br>
                        Đơn giá: <strong><?= number_format($previewData['donGiaCoBan']) ?>đ</strong>
                    </div>

                    <?php if ($previewData['phuThuKhach3'] > 0): ?>
                    <div class="calculation-step">
                        <strong>2. Phụ thu khách thứ 3 (<?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%):</strong><br>
                        <?= number_format($previewData['donGiaCoBan']) ?>đ × 25% = 
                        <strong>+<?= number_format($previewData['phuThuKhach3']) ?>đ</strong>
                    </div>
                    <?php endif; ?>

                    <?php if ($previewData['heSoNN'] > 0): ?>
                    <div class="calculation-step">
                        <strong>3. Hệ số khách nước ngoài (×<?= $previewData['heSoNN'] ?>):</strong><br>
                        <?= number_format($previewData['donGiaCoBan'] + $previewData['phuThuKhach3']) ?>đ × <?= $previewData['heSoNN'] ?> = 
                        <strong><?= number_format($previewData['donGiaTinh']) ?>đ</strong>
                    </div>
                    <?php endif; ?>

                    <div class="calculation-step">
                        <strong>4. Tính theo số ngày thuê:</strong><br>
                        Từ: <?= date('d/m/Y', strtotime($previewData['phieuThue']['NgayBatDauThue'])) ?><br>
                        Đến: <?= date('d/m/Y', strtotime($previewData['ngayThanhToan'])) ?><br>
                        Số ngày: <strong><?= $previewData['soNgay'] ?> ngày</strong>
                    </div>

                    <div class="total-box">
                        <div><i class="ph ph-currency-circle-dollar"></i> TỔNG THÀNH TIỀN</div>
                        <div style="font-size: 1.5em; font-weight: bold; margin-top: 10px;">
                            <?= number_format($previewData['donGiaTinh']) ?>đ × <?= $previewData['soNgay'] ?> ngày = 
                            <?= number_format($previewData['thanhTien']) ?>đ
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="confirm">
                        <input type="hidden" name="maPhieuThue" value="<?= $previewData['phieuThue']['MaPhieuThue'] ?>">
                        <input type="hidden" name="tenKH" value="<?= htmlspecialchars($previewData['tenKH']) ?>">
                        <input type="hidden" name="diaChi" value="<?= htmlspecialchars($previewData['diaChi']) ?>">
                        <input type="hidden" name="ngayThanhToan" value="<?= $previewData['ngayThanhToan'] ?>">
                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1em;">
                            <i class="ph ph-check-circle"></i> Xác Nhận Lập Hóa Đơn
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="ph ph-list-bullets"></i> Danh Sách Hóa Đơn Gần Đây
                    </h2>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phiếu Thuê</th>
                            <th>Phòng</th>
                            <th>Khách Hàng</th>
                            <th>Ngày TT</th>
                            <th>Trị Giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hoaDons as $hd): ?>
                        <tr>
                            <td><strong>#<?= $hd['MaHoaDon'] ?></strong></td>
                            <td>PT#<?= $hd['MaPhieuThue'] ?></td>
                            <td>Phòng <?= htmlspecialchars($hd['SoPhong']) ?></td>
                            <td><?= htmlspecialchars($hd['TenKhachHangCoQuan']) ?></td>
                            <td><?= date('d/m/Y', strtotime($hd['NgayThanhToan'])) ?></td>
                            <td><strong><?= number_format($hd['TriGia']) ?>đ</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
