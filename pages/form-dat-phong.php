<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/KhachHangController.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';

$database = new Database();
$db = $database->connect();

$message = '';
$maPhong = isset($_GET['phong']) ? $_GET['phong'] : null;

if (!$maPhong) {
    header('Location: ../index.php');
    exit;
}

// Lấy thông tin phòng
$stmt = $db->prepare("SELECT P.*, L.TenLoai, L.DonGiaCoBan 
                      FROM PHONG P 
                      JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                      WHERE P.MaPhong = ? AND P.TinhTrang = 'Trống'");
$stmt->execute([$maPhong]);
$phong = $stmt->fetch();

if (!$phong) {
    header('Location: ../index.php');
    exit;
}

// Lấy tham số
$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');

// Xử lý đặt phòng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $khachController = new KhachHangController();
        $phieuController = new PhieuThueController();
        
        $soKhach = (int)$_POST['so_khach'];
        
        if ($soKhach < 1 || $soKhach > $soKhachToiDa) {
            throw new Exception("Số khách phải từ 1 đến $soKhachToiDa người");
        }
        
        $db->beginTransaction();
        
        // Thêm khách hàng và lưu ID
        $danhSachKhachID = [];
        
        for ($i = 0; $i < $soKhach; $i++) {
            $tenKhach = $_POST["ten_khach_$i"];
            $loaiKhach = $_POST["loai_khach_$i"];
            $cmnd = $_POST["cmnd_$i"];
            $diaChi = $_POST["dia_chi_$i"];
            
            $maKhach = $khachController->themKhachHang($tenKhach, $loaiKhach, $cmnd, $diaChi);
            $danhSachKhachID[] = $maKhach;
        }
        
        // Tạo phiếu thuê
        $ngayBatDau = $_POST['ngay_bat_dau'];
        $maPhieuThue = $phieuController->taoPhieuThue($maPhong, $ngayBatDau, $danhSachKhachID);
        
        $db->commit();
        
        header("Location: xac-nhan-dat-phong.php?phieu=$maPhieuThue");
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-error">' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng <?= $phong['SoPhong'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
    <style>
        .booking-form {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .room-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .room-summary h2 {
            margin: 0 0 10px 0;
        }
        .customer-section {
            border: 2px solid #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            background: #f8f9fa;
        }
        .customer-section h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>📝 Điền thông tin đặt phòng</h1>
            </div>
        </header>

        <main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
            <?= $message ?>
            
            <div class="booking-form">
                <div class="room-summary">
                    <h2>🏨 Phòng <?= $phong['SoPhong'] ?> - <?= $phong['TenLoai'] ?></h2>
                    <p>💰 Giá: <strong><?= number_format($phong['DonGiaCoBan']) ?>đ</strong>/đêm</p>
                    <p>👥 Tối đa: <?= $soKhachToiDa ?> khách</p>
                </div>

                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label>📅 Ngày nhận phòng:</label>
                        <input type="date" name="ngay_bat_dau" required 
                               min="<?= date('Y-m-d') ?>" 
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group">
                        <label>👥 Số lượng khách:</label>
                        <select name="so_khach" id="soKhach" required onchange="updateCustomerForms()">
                            <?php for($i = 1; $i <= $soKhachToiDa; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> khách</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div id="customerForms">
                        <!-- Sẽ được tạo bằng JavaScript -->
                    </div>

                    <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 1.1em; margin-top: 20px;">
                        ✅ Xác nhận đặt phòng
                    </button>
                    <a href="../index.php" class="btn" style="width: 100%; padding: 15px; text-align: center; display: block; margin-top: 10px; background: #999;">
                        ❌ Hủy
                    </a>
                </form>
            </div>
        </main>
    </div>

    <script>
        function updateCustomerForms() {
            const soKhach = parseInt(document.getElementById('soKhach').value);
            const container = document.getElementById('customerForms');
            container.innerHTML = '';
            
            for (let i = 0; i < soKhach; i++) {
                const section = document.createElement('div');
                section.className = 'customer-section';
                section.innerHTML = `
                    <h3>👤 Khách hàng ${i + 1}</h3>
                    <div class="form-group">
                        <label>Họ và tên:</label>
                        <input type="text" name="ten_khach_${i}" required>
                    </div>
                    <div class="form-group">
                        <label>Loại khách:</label>
                        <select name="loai_khach_${i}" required>
                            <option value="Nội địa">🇻🇳 Nội địa</option>
                            <option value="Nước ngoài">🌍 Nước ngoài</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>CMND/Passport:</label>
                        <input type="text" name="cmnd_${i}" required>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ:</label>
                        <input type="text" name="dia_chi_${i}" required>
                    </div>
                `;
                container.appendChild(section);
            }
        }
        
        // Khởi tạo form cho 1 khách
        updateCustomerForms();
    </script>
</body>
</html>
