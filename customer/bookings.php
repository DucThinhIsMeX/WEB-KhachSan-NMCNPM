<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';
require_once __DIR__ . '/../config/database.php';

$auth = new CustomerAuthController();

// Kiểm tra đăng nhập
if (!$auth->isLoggedIn()) {
header('Location: login.php');
exit;
}

$customerInfo = $auth->getCustomerInfo();
$database = new Database();
$db = $database->connect();

// Lấy lịch sử đặt phòng của khách hàng này (dựa trên email hoặc CMND)
try {
$stmt = $db->prepare("
SELECT DISTINCT 
PT.MaPhieuThue,
PT.NgayBatDauThue,
PT.SoDem,
PT.TinhTrangPhieu,
P.SoPhong,
L.TenLoai,
L.DonGiaCoBan,
H.NgayThanhToan,
H.TriGia
FROM PHIEUTHUE PT
JOIN PHONG P ON PT.MaPhong = P.MaPhong
JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong
LEFT JOIN HOADON H ON PT.MaPhieuThue = H.MaPhieuThue
WHERE PT.MaKhachHangUser = ?
ORDER BY PT.NgayBatDauThue DESC
");
$stmt->execute([$_SESSION['customer_id']]);
$bookings = $stmt->fetchAll();

} catch (Exception $e) {
$bookings = [];
$error = "Không thể tải lịch sử đặt phòng: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lịch Sử Đặt Phòng</title>
<link rel="stylesheet" href="../assets/css/booking.css">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
.bookings-container {
min-height: 100vh;
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
padding: 40px 20px;
}
.bookings-header {
max-width: 1200px;
margin: 0 auto 30px;
text-align: center;
color: white;
}
.bookings-header h1 {
font-size: 2.5em;
margin-bottom: 10px;
}
.bookings-content {
max-width: 1200px;
margin: 0 auto;
}
.booking-card {
background: white;
border-radius: 15px;
padding: 25px;
margin-bottom: 20px;
box-shadow: 0 4px 12px rgba(0,0,0,0.1);
transition: 0.3s;
}
.booking-card:hover {
transform: translateY(-5px);
box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
.booking-header {
display: flex;
justify-content: space-between;
align-items: start;
margin-bottom: 20px;
padding-bottom: 15px;
border-bottom: 2px solid #f0f0f0;
}
.booking-id {
font-size: 1.3em;
color: var(--primary);
font-weight: 700;
}
.booking-status {
padding: 8px 16px;
border-radius: 20px;
font-weight: 600;
font-size: 0.9em;
}
.status-active {
background: #fff3cd;
color: #856404;
}
.status-completed {
background: #d4edda;
color: #155724;
}
.status-cancelled {
background: #f8d7da;
color: #721c24;
}
.booking-details {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
gap: 15px;
}
.detail-item {
display: flex;
align-items: center;
gap: 10px;
padding: 12px;
background: #f8f9fa;
border-radius: 8px;
}
.detail-item i {
color: var(--primary);
font-size: 1.3em;
}
.detail-label {
color: #666;
font-size: 0.9em;
}
.detail-value {
color: var(--dark);
font-weight: 600;
}
.empty-state {
background: white;
border-radius: 20px;
padding: 60px 20px;
text-align: center;
}
.empty-icon {
font-size: 5em;
color: #ccc;
margin-bottom: 20px;
}
.back-btn {
display: inline-flex;
align-items: center;
gap: 8px;
padding: 12px 24px;
background: white;
color: var(--primary);
text-decoration: none;
border-radius: 12px;
font-weight: 600;
margin-top: 20px;
transition: 0.3s;
}
.back-btn:hover {
background: var(--primary);
color: white;
transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="bookings-container">
<div class="bookings-header">
<h1><i class="ph ph-ticket"></i> Lịch Sử Đặt Phòng</h1>
<p>Xem lại các lần đặt phòng của bạn</p>
</div>
<div class="bookings-content">
<?php if (count($bookings) > 0): ?>
<?php foreach ($bookings as $booking): 
$statusClass = '';
$statusText = '';
switch ($booking['TinhTrangPhieu']) {
case 'Đang thuê':
$statusClass = 'status-active';
$statusText = 'Đang thuê';
break;
case 'Đã thanh toán':
$statusClass = 'status-completed';
$statusText = 'Hoàn tất';
break;
case 'Đã hủy':
$statusClass = 'status-cancelled';
$statusText = 'Đã hủy';
break;
}
?>
<div class="booking-card">
<div class="booking-header">
<div class="booking-id">
<i class="ph ph-ticket"></i> Mã đặt phòng: #<?= $booking['MaPhieuThue'] ?>
</div>
<div class="booking-status <?= $statusClass ?>">
<?= $statusText ?>
</div>
</div>
<div class="booking-details">
<div class="detail-item">
<i class="ph ph-bed"></i>
<div>
<div class="detail-label">Phòng</div>
<div class="detail-value"><?= htmlspecialchars($booking['SoPhong']) ?> - <?= htmlspecialchars($booking['TenLoai']) ?></div>
</div>
</div>
<div class="detail-item">
<i class="ph ph-calendar"></i>
<div>
<div class="detail-label">Ngày đặt</div>
<div class="detail-value"><?= date('d/m/Y', strtotime($booking['NgayBatDauThue'])) ?></div>
</div>
</div>
<div class="detail-item">
<i class="ph ph-currency-circle-dollar"></i>
<div>
<div class="detail-label">Đơn giá</div>
<div class="detail-value"><?= number_format($booking['DonGiaCoBan']) ?>đ/đêm</div>
</div>
</div>
<?php if ($booking['NgayThanhToan']): ?>
<div class="detail-item">
<i class="ph ph-check-circle"></i>
<div>
<div class="detail-label">Thanh toán</div>
<div class="detail-value"><?= date('d/m/Y', strtotime($booking['NgayThanhToan'])) ?></div>
</div>
</div>
<div class="detail-item">
<i class="ph ph-wallet"></i>
<div>
<div class="detail-label">Tổng tiền</div>
<div class="detail-value" style="color: #e74c3c;"><?= number_format($booking['TriGia']) ?>đ</div>
</div>
</div>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty-state">
<i class="ph ph-calendar-x empty-icon"></i>
<h2>Chưa Có Lịch Sử Đặt Phòng</h2>
<p>Bạn chưa đặt phòng nào. Hãy khám phá các phòng trống và đặt phòng ngay!</p>
<a href="../index.php" class="btn btn-primary" style="display: inline-flex; margin-top: 20px;">
<i class="ph ph-magnifying-glass"></i>
<span>Tìm phòng</span>
</a>
</div>
<?php endif; ?>
<div style="text-align: center; margin-top: 30px;">
<a href="../index.php" class="back-btn">
<i class="ph ph-arrow-left"></i>
<span>Quay lại trang chủ</span>
</a>
<a href="profile.php" class="back-btn">
<i class="ph ph-user"></i>
<span>Thông tin cá nhân</span>
</a>
</div>
</div>
</div>
</body>
</html>