<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../controllers/PhongController.php';
require_once __DIR__ . '/../controllers/KhachHangController.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$phieuThueCtrl = new PhieuThueController();
$controller = new PhongController();
$khachHangCtrl = new KhachHangController();
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Xử lý tạo phiếu thuê
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
try {
// Thêm khách hàng vào database
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
$soDem = isset($_POST['soDem']) ? (int)$_POST['soDem'] : 1;
$soDem = max(1, min(14, $soDem));
// Tạo phiếu thuê
$maPhieuThue = $phieuThueCtrl->taoPhieuThue(
    $_POST['maPhong'],
    $_POST['ngayBatDau'],
    $soDem,
    $danhSachKhach,
    null // admin tạo phiếu thuê thì không gắn customer user
);
$message = "✅ Tạo phiếu thuê #$maPhieuThue thành công!";
} catch (Exception $e) {
$error = "❌ Lỗi: " . $e->getMessage();
}
}

// Xử lý hủy phiếu thuê
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
try {
$stmt = $db->prepare("UPDATE PHIEUTHUE SET TinhTrangPhieu = 'Đã hủy' WHERE MaPhieuThue = ?");
$stmt->execute([$_GET['id']]);
// Cập nhật phòng về trống
$stmt = $db->prepare("UPDATE PHONG SET TinhTrang = 'Trống' 
WHERE MaPhong = (SELECT MaPhong FROM PHIEUTHUE WHERE MaPhieuThue = ?)");
$stmt->execute([$_GET['id']]);
$message = "✅ Hủy phiếu thuê thành công!";
} catch (Exception $e) {
$error = "❌ Lỗi: " . $e->getMessage();
}
}

$phongsTrong = $controller->traCuuPhong(null, 'Trống');
$phieuThues = $phieuThueCtrl->getPhieuThue();
$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');

$page_title = 'Phiếu Thuê';
$phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Phiếu Thuê</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
.khach-section {
border: 2px solid #667eea;
padding: 20px;
margin: 20px 0;
border-radius: 10px;
background: #f8f9ff;
}
.khach-section h4 {
color: #667eea;
margin-bottom: 15px;
}
.khach-section.hidden {
display: none;
}
.toggle-khach {
background: #667eea;
color: white;
border: none;
padding: 10px 20px;
border-radius: 5px;
cursor: pointer;
margin: 10px 5px;
}
.toggle-khach:hover {
background: #5568d3;
}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

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
<i class="ph ph-plus-circle"></i> Tạo Phiếu Thuê Mới
</h2>
</div>

<form method="POST" id="formPhieuThue" style="max-width: 900px;">
<input type="hidden" name="action" value="create">
<div class="form-group">
<label><i class="ph ph-bed"></i> Chọn Phòng Trống:</label>
<select name="maPhong" required class="form-control">
<option value="">-- Chọn phòng --</option>
<?php foreach ($phongsTrong as $phong): ?>
<option value="<?= $phong['MaPhong'] ?>">
Phòng <?= htmlspecialchars($phong['SoPhong']) ?> - 
<?= htmlspecialchars($phong['TenLoai']) ?> 
(<?= number_format($phong['DonGiaCoBan']) ?>đ/đêm)
</option>
<?php endforeach; ?>
</select>
</div>

<div class="form-group">
<label><i class="ph ph-calendar"></i> Ngày Bắt Đầu Thuê:</label>
<input type="date" name="ngayBatDau" 
value="<?= date('Y-m-d') ?>" 
min="<?= date('Y-m-d') ?>"
required class="form-control">
</div>

<hr style="margin: 30px 0;">
<h3 style="color: #667eea;">
<i class="ph ph-users"></i> Thông Tin Khách Hàng
</h3>
<p style="color: #666; margin-bottom: 20px;">
<strong><i class="ph ph-warning"></i> Lưu ý:</strong> Tối đa <?= $soKhachToiDa ?> khách/phòng. 
Khách thứ 3 sẽ phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%.
</p>

<!-- Khách 1 -->
<div class="khach-section">
<h4><i class="ph ph-user"></i> Khách Hàng 1 (Bắt buộc)</h4>
<div class="form-group">
<label>Họ và Tên:</label>
<input type="text" name="tenKhach1" required class="form-control">
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
<div class="form-group">
<label>Loại Khách:</label>
<select name="loaiKhach1" required class="form-control">
<option value="Nội địa">Nội địa</option>
<option value="Nước ngoài">Nước ngoài</option>
</select>
</div>
<div class="form-group">
<label>CMND/CCCD:</label>
<input type="text" name="cmnd1" required class="form-control">
</div>
</div>
<div class="form-group">
<label><i class="ph ph-map-pin"></i> Địa Chỉ:</label>
<input type="text" name="diaChi1" required class="form-control">
</div>
</div>

<!-- Khách 2 -->
<div class="khach-section hidden" id="khach2Section">
<h4><i class="ph ph-user"></i> Khách Hàng 2 (Tùy chọn)</h4>
<div class="form-group">
<label>Họ và Tên:</label>
<input type="text" name="tenKhach2" class="form-control">
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
<div class="form-group">
<label>Loại Khách:</label>
<select name="loaiKhach2" class="form-control">
<option value="Nội địa">Nội địa</option>
<option value="Nước ngoài">Nước ngoài</option>
</select>
</div>
<div class="form-group">
<label>CMND/CCCD:</label>
<input type="text" name="cmnd2" class="form-control">
</div>
</div>
<div class="form-group">
<label>Địa Chỉ:</label>
<input type="text" name="diaChi2" class="form-control">
</div>
</div>

<!-- Khách 3 -->
<div class="khach-section hidden" id="khach3Section">
<h4><i class="ph ph-user"></i> Khách Hàng 3 (Phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%)</h4>
<div class="form-group">
<label>Họ và Tên:</label>
<input type="text" name="tenKhach3" class="form-control">
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
<div class="form-group">
<label>Loại Khách:</label>
<select name="loaiKhach3" class="form-control">
<option value="Nội địa">Nội địa</option>
<option value="Nước ngoài">Nước ngoài</option>
</select>
</div>
<div class="form-group">
<label>CMND/CCCD:</label>
<input type="text" name="cmnd3" class="form-control">
</div>
</div>
<div class="form-group">
<label>Địa Chỉ:</label>
<input type="text" name="diaChi3" class="form-control">
</div>
</div>

<div style="text-align: center; margin: 20px 0;">
<button type="button" class="toggle-khach" id="btnKhach2" onclick="toggleKhach(2)">
<i class="ph ph-plus"></i> Thêm Khách 2
</button>
<button type="button" class="toggle-khach hidden" id="btnKhach3" onclick="toggleKhach(3)">
<i class="ph ph-plus"></i> Thêm Khách 3
</button>
</div>

<button type="submit" class="btn btn-primary">
<i class="ph ph-check-circle"></i> Tạo Phiếu Thuê
</button>
</form>
</div>

<div class="content-section">
<div class="section-header">
<h2 class="section-title">
<i class="ph ph-list-bullets"></i> Danh Sách Phiếu Thuê
</h2>
</div>
<table class="data-table">
<thead>
<tr>
<th>Mã PT</th>
<th>Phòng</th>
<th>Ngày Thuê</th>
<th>Khách</th>
<th>Trạng Thái</th>
<th>Thao Tác</th>
</tr>
</thead>
<tbody>
<?php foreach ($phieuThues as $pt): 
$khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
?>
<tr>
<td><strong>#<?= $pt['MaPhieuThue'] ?></strong></td>
<td>Phòng <?= htmlspecialchars($pt['SoPhong']) ?></td>
<td><?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?></td>
<td>
<?php foreach ($khachs as $k): ?>
<div><i class="ph ph-user"></i> <?= htmlspecialchars($k['TenKhach']) ?> 
<small>(<?= htmlspecialchars($k['LoaiKhach']) ?>)</small>
</div>
<?php endforeach; ?>
</td>
<td>
<span class="status-badge <?= $pt['TinhTrangPhieu'] === 'Đang thuê' ? 'occupied' : 'available' ?>">
<?= htmlspecialchars($pt['TinhTrangPhieu']) ?>
</span>
</td>
<td>
<?php if ($pt['TinhTrangPhieu'] === 'Đang thuê'): ?>
<a href="?action=cancel&id=<?= $pt['MaPhieuThue'] ?>" 
class="btn btn-sm btn-danger"
onclick="return confirm('Xác nhận hủy phiếu thuê?')">
<i class="ph ph-x-circle"></i> Hủy
</a>
<?php else: ?>
<span style="color: #999;">-</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</main>
</div>

<script>
function toggleKhach(soKhach) {
const section = document.getElementById('khach' + soKhach + 'Section');
const btn = document.getElementById('btnKhach' + soKhach);
if (section.classList.contains('hidden')) {
section.classList.remove('hidden');
btn.innerHTML = '<i class="ph ph-minus"></i> Bỏ Khách ' + soKhach;
btn.style.background = '#dc3545';
if (soKhach === 2) {
document.getElementById('btnKhach3').classList.remove('hidden');
}
} else {
section.classList.add('hidden');
btn.innerHTML = '<i class="ph ph-plus"></i> Thêm Khách ' + soKhach;
btn.style.background = '#667eea';
section.querySelectorAll('input, select').forEach(input => {
if (input.type !== 'hidden') {
input.value = input.tagName === 'SELECT' ? 'Nội địa' : '';
}
});
if (soKhach === 2) {
document.getElementById('btnKhach3').classList.add('hidden');
document.getElementById('khach3Section').classList.add('hidden');
}
}
}

document.getElementById('formPhieuThue').addEventListener('submit', function(e) {
const ngay = document.querySelector('input[name="ngayBatDau"]').value;
if (!ngay) {
e.preventDefault();
alert('Vui lòng chọn ngày bắt đầu thuê!');
return false;
}
return confirm('Xác nhận tạo phiếu thuê?');
});
</script>
</body>
</html>
