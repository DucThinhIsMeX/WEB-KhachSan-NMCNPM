<?php
require_once 'config/database.php';
require_once 'controllers/PhongController.php';

$database = new Database();
$db = $database->connect();
$phongController = new PhongController();

// Lấy tham số hệ thống
$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');

// Lấy danh sách loại phòng
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG ORDER BY DonGiaCoBan")->fetchAll();

// Lấy phòng trống theo loại (nếu có filter)
$loaiPhongFilter = isset($_GET['loai']) ? $_GET['loai'] : null;
$phongsTrong = $phongController->traCuuPhong($loaiPhongFilter, 'Trống');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng - Khách sạn</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/datphong.css">
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>🏨 Đặt Phòng Khách Sạn</h1>
                <p>Chọn phòng phù hợp với nhu cầu của bạn</p>
            </div>
            <nav class="booking-nav">
                <a href="index.php">Trang chủ</a>
                <a href="datphong.php" class="active">Đặt phòng</a>
                <a href="pages/tra-cuu-dat-phong.php">Tra cứu đặt phòng</a>
            </nav>
        </header>

        <main class="booking-main">
            <!-- Bộ lọc loại phòng -->
            <section class="filter-section">
                <h2>Chọn loại phòng</h2>
                <div class="room-types">
                    <a href="datphong.php" class="room-type-card <?= !$loaiPhongFilter ? 'active' : '' ?>">
                        <div class="card-icon">🏠</div>
                        <h3>Tất cả</h3>
                        <p><?= count($phongController->traCuuPhong(null, 'Trống')) ?> phòng</p>
                    </a>
                    <?php foreach ($loaiPhongs as $loai): 
                        $soPhong = count($phongController->traCuuPhong($loai['MaLoaiPhong'], 'Trống'));
                    ?>
                    <a href="datphong.php?loai=<?= $loai['MaLoaiPhong'] ?>" 
                       class="room-type-card <?= $loaiPhongFilter == $loai['MaLoaiPhong'] ? 'active' : '' ?>">
                        <div class="card-icon">
                            <?php
                            if ($loai['TenLoai'] == 'Loại A') echo '🛏️';
                            else if ($loai['TenLoai'] == 'Loại B') echo '🛋️';
                            else echo '👑';
                            ?>
                        </div>
                        <h3><?= $loai['TenLoai'] ?></h3>
                        <p class="price"><?= number_format($loai['DonGiaCoBan']) ?>đ/đêm</p>
                        <p><?= $soPhong ?> phòng trống</p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Danh sách phòng trống -->
            <section class="rooms-section">
                <h2>Phòng có sẵn (<?= count($phongsTrong) ?> phòng)</h2>
                
                <?php if (count($phongsTrong) > 0): ?>
                <div class="rooms-grid">
                    <?php foreach ($phongsTrong as $phong): ?>
                    <div class="room-card">
                        <div class="room-image">
                            <?php
                            // Icon theo loại phòng
                            if ($phong['TenLoai'] == 'Loại A') {
                                echo '<div class="room-icon">🛏️</div>';
                            } else if ($phong['TenLoai'] == 'Loại B') {
                                echo '<div class="room-icon">🛋️</div>';
                            } else {
                                echo '<div class="room-icon">👑</div>';
                            }
                            ?>
                            <span class="room-number">Phòng <?= $phong['SoPhong'] ?></span>
                        </div>
                        <div class="room-details">
                            <h3><?= $phong['TenLoai'] ?></h3>
                            <div class="room-info">
                                <p>📍 Số phòng: <strong><?= $phong['SoPhong'] ?></strong></p>
                                <p>💰 Giá: <strong class="price"><?= number_format($phong['DonGiaCoBan']) ?>đ</strong>/đêm</p>
                                <p>👥 Tối đa: <strong><?= $soKhachToiDa ?> khách</strong></p>
                                <?php if ($phong['GhiChu']): ?>
                                <p>📝 <?= $phong['GhiChu'] ?></p>
                                <?php endif; ?>
                            </div>
                            <a href="pages/form-dat-phong.php?phong=<?= $phong['MaPhong'] ?>" class="btn-book">
                                Đặt phòng ngay
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-rooms">
                    <p>😔 Hiện tại không có phòng trống trong loại này</p>
                    <a href="datphong.php" class="btn">Xem tất cả phòng</a>
                </div>
                <?php endif; ?>
            </section>

            <!-- Thông tin thêm -->
            <section class="info-section">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-icon">⏰</div>
                        <h3>Nhận phòng</h3>
                        <p>Từ 14:00</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">🚪</div>
                        <h3>Trả phòng</h3>
                        <p>Trước 12:00</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">💳</div>
                        <h3>Thanh toán</h3>
                        <p>Tiền mặt, Thẻ</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">📞</div>
                        <h3>Hỗ trợ 24/7</h3>
                        <p>1900-xxxx</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="booking-footer">
            <p>&copy; 2024 Khách sạn - Hệ thống đặt phòng trực tuyến</p>
        </footer>
    </div>
</body>
</html>
