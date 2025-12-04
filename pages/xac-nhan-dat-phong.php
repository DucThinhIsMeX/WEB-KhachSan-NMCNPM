<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

$maPhieuThue = isset($_GET['phieu']) ? $_GET['phieu'] : null;

if (!$maPhieuThue) {
    header('Location: ../index.php');
    exit;
}

// Lấy thông tin phiếu thuê
$stmt = $db->prepare("SELECT PT.*, P.SoPhong, L.TenLoai, L.DonGiaCoBan 
                      FROM PHIEUTHUE PT 
                      JOIN PHONG P ON PT.MaPhong = P.MaPhong 
                      JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                      WHERE PT.MaPhieuThue = ?");
$stmt->execute([$maPhieuThue]);
$phieuThue = $stmt->fetch();

// Lấy danh sách khách
$stmt = $db->prepare("SELECT K.* FROM KHACHHANG K 
                      JOIN CHITIET_THUE CT ON K.MaKhachHang = CT.MaKhachHang 
                      WHERE CT.MaPhieuThue = ?");
$stmt->execute([$maPhieuThue]);
$khachHangs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt phòng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 5em;
            color: #28a745;
            margin-bottom: 20px;
        }
        .booking-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .customer-list {
            background: white;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>✅ Đặt phòng thành công</h1>
            </div>
        </header>

        <main style="padding: 20px;">
            <div class="success-container">
                <div class="success-icon">✅</div>
                <h2 style="color: #28a745; margin-bottom: 10px;">Đặt phòng thành công!</h2>
                <p style="color: #666; font-size: 1.1em;">Mã phiếu thuê của bạn là: <strong style="color: #667eea; font-size: 1.3em;">#<?= $maPhieuThue ?></strong></p>

                <div class="booking-details">
                    <h3 style="color: #667eea; margin-bottom: 15px;">📋 Thông tin đặt phòng</h3>
                    
                    <div class="detail-row">
                        <span>🏨 Phòng số:</span>
                        <strong><?= $phieuThue['SoPhong'] ?></strong>
                    </div>
                    
                    <div class="detail-row">
                        <span>🛏️ Loại phòng:</span>
                        <strong><?= $phieuThue['TenLoai'] ?></strong>
                    </div>
                    
                    <div class="detail-row">
                        <span>💰 Đơn giá:</span>
                        <strong><?= number_format($phieuThue['DonGiaCoBan']) ?>đ/đêm</strong>
                    </div>
                    
                    <div class="detail-row">
                        <span>📅 Ngày nhận phòng:</span>
                        <strong><?= date('d/m/Y', strtotime($phieuThue['NgayBatDauThue'])) ?></strong>
                    </div>
                    
                    <div class="detail-row">
                        <span>📊 Tình trạng:</span>
                        <strong style="color: #28a745;"><?= $phieuThue['TinhTrangPhieu'] ?></strong>
                    </div>

                    <div class="customer-list">
                        <h4 style="color: #333; margin-bottom: 15px;">👥 Danh sách khách hàng (<?= count($khachHangs) ?> người)</h4>
                        <?php foreach ($khachHangs as $index => $khach): ?>
                        <div style="padding: 10px; background: #f0f0f0; border-radius: 5px; margin-bottom: 10px;">
                            <strong><?= $index + 1 ?>. <?= $khach['TenKhach'] ?></strong>
                            <?php if ($khach['LoaiKhach'] == 'Nước ngoài'): ?>
                                <span style="background: #667eea; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 5px;">🌍 Nước ngoài</span>
                            <?php else: ?>
                                <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 5px;">🇻🇳 Nội địa</span>
                            <?php endif; ?>
                            <div style="font-size: 0.9em; color: #666; margin-top: 5px;">
                                📄 CMND: <?= $khach['CMND'] ?> | 📍 <?= $khach['DiaChi'] ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 20px; margin: 25px 0;">
                    <h4 style="color: #856404; margin-bottom: 10px;">⚠️ Lưu ý quan trọng</h4>
                    <ul style="text-align: left; color: #856404; line-height: 1.8;">
                        <li>Vui lòng đến trước <strong>14:00</strong> để nhận phòng</li>
                        <li>Mang theo CMND/Passport để xác thực</li>
                        <li>Thanh toán khi trả phòng</li>
                        <li>Liên hệ: <strong>1900-xxxx</strong> nếu cần hỗ trợ</li>
                    </ul>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <a href="../index.php" class="btn" style="flex: 1; padding: 15px;">
                        🏠 Về trang chủ
                    </a>
                    <a href="tra-cuu-dat-phong.php" class="btn" style="flex: 1; padding: 15px; background: #28a745;">
                        🔍 Tra cứu đặt phòng
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
