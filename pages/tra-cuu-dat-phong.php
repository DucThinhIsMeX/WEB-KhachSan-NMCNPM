<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maPhieuThue = $_POST['ma_phieu_thue'];
    $cmnd = $_POST['cmnd'];
    
    // Tìm phiếu thuê
    $stmt = $db->prepare("SELECT PT.*, P.SoPhong, L.TenLoai, L.DonGiaCoBan 
                          FROM PHIEUTHUE PT 
                          JOIN PHONG P ON PT.MaPhong = P.MaPhong 
                          JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                          WHERE PT.MaPhieuThue = ?");
    $stmt->execute([$maPhieuThue]);
    $phieuThue = $stmt->fetch();
    
    if ($phieuThue) {
        // Kiểm tra CMND
        $stmt = $db->prepare("SELECT COUNT(*) FROM CHITIET_THUE CT 
                              JOIN KHACHHANG K ON CT.MaKhachHang = K.MaKhachHang 
                              WHERE CT.MaPhieuThue = ? AND K.CMND = ?");
        $stmt->execute([$maPhieuThue, $cmnd]);
        
        if ($stmt->fetchColumn() > 0) {
            // Lấy danh sách khách
            $stmt = $db->prepare("SELECT K.* FROM KHACHHANG K 
                                  JOIN CHITIET_THUE CT ON K.MaKhachHang = CT.MaKhachHang 
                                  WHERE CT.MaPhieuThue = ?");
            $stmt->execute([$maPhieuThue]);
            $result = [
                'phieu' => $phieuThue,
                'khach' => $stmt->fetchAll()
            ];
        } else {
            $error = 'CMND không khớp với phiếu thuê này';
        }
    } else {
        $error = 'Không tìm thấy phiếu thuê';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đặt phòng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>🔍 Tra cứu đặt phòng</h1>
                <p>Nhập mã phiếu thuê và CMND để tra cứu</p>
            </div>
            <nav class="booking-nav">
                <a href="../index.php">Trang chủ</a>
                <a href="../datphong.php">Đặt phòng</a>
                <a href="tra-cuu-dat-phong.php" class="active">Tra cứu</a>
            </nav>
        </header>

        <main style="max-width: 800px; margin: 40px auto; padding: 20px;">
            <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <form method="POST">
                    <div class="form-group">
                        <label>Mã phiếu thuê:</label>
                        <input type="number" name="ma_phieu_thue" required placeholder="Ví dụ: 1">
                    </div>
                    <div class="form-group">
                        <label>CMND/Passport:</label>
                        <input type="text" name="cmnd" required placeholder="Nhập CMND của bạn">
                    </div>
                    <button type="submit" class="btn" style="width: 100%; padding: 15px;">
                        🔍 Tra cứu
                    </button>
                </form>

                <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top: 20px;">
                    <?= $error ?>
                </div>
                <?php endif; ?>

                <?php if ($result): ?>
                <div style="margin-top: 30px; padding: 25px; background: #f8f9fa; border-radius: 10px;">
                    <h3 style="color: #667eea; margin-bottom: 15px;">✅ Thông tin đặt phòng</h3>
                    
                    <p><strong>Mã phiếu:</strong> #<?= $result['phieu']['MaPhieuThue'] ?></p>
                    <p><strong>Phòng số:</strong> <?= $result['phieu']['SoPhong'] ?></p>
                    <p><strong>Loại phòng:</strong> <?= $result['phieu']['TenLoai'] ?></p>
                    <p><strong>Đơn giá:</strong> <?= number_format($result['phieu']['DonGiaCoBan']) ?>đ/đêm</p>
                    <p><strong>Ngày nhận:</strong> <?= date('d/m/Y', strtotime($result['phieu']['NgayBatDauThue'])) ?></p>
                    <p><strong>Tình trạng:</strong> <span style="color: #28a745;"><?= $result['phieu']['TinhTrangPhieu'] ?></span></p>
                    
                    <h4 style="margin-top: 20px; color: #333;">Danh sách khách:</h4>
                    <?php foreach ($result['khach'] as $index => $k): ?>
                    <div style="background: white; padding: 10px; border-radius: 5px; margin: 5px 0;">
                        <?= $index + 1 ?>. <?= $k['TenKhach'] ?> (<?= $k['LoaiKhach'] ?>)
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
