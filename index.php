<?php
require_once 'config/database.php';
require_once 'controllers/PhongController.php';

$database = new Database();

// Kiểm tra database đã được khởi tạo chưa
if (!$database->isDatabaseInitialized()) {
    header('Location: database/init.php');
    exit;
}

$db = $database->connect();

// Kiểm tra bảng LOAIPHONG có tồn tại không
try {
    $db->query("SELECT 1 FROM LOAIPHONG LIMIT 1");
} catch(PDOException $e) {
    // Database chưa được khởi tạo đúng, redirect đến init
    header('Location: database/init.php');
    exit;
}

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
    <title>Đặt phòng Khách sạn</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/datphong.css">
    <style>
        .admin-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: 0.3s;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }
        .hero-section h1 {
            font-size: 3em;
            margin-bottom: 15px;
        }
        .hero-section p {
            font-size: 1.3em;
            margin-bottom: 30px;
        }
        .quick-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .quick-stat {
            background: rgba(255,255,255,0.2);
            padding: 20px 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .quick-stat h3 {
            font-size: 2.5em;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Nút Admin link đến port 8000 -->
    <a href="http://localhost:8000" class="admin-link" target="_blank">
        <span>🔐</span>
        <span>Quản trị</span>
    </a>

    <div class="booking-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <h1>🏨 Chào mừng đến Khách sạn</h1>
            <p>Đặt phòng dễ dàng - Trải nghiệm tuyệt vời</p>
            <div class="quick-stats">
                <div class="quick-stat">
                    <h3><?= count($loaiPhongs) ?></h3>
                    <p>Loại phòng</p>
                </div>
                <div class="quick-stat">
                    <h3><?= count($phongsTrong) ?></h3>
                    <p>Phòng trống</p>
                </div>
                <div class="quick-stat">
                    <h3>24/7</h3>
                    <p>Hỗ trợ</p>
                </div>
            </div>
        </section>

        <!-- Navigation -->
        <header class="booking-header">
            <nav class="booking-nav">
                <a href="index.php" class="active">🏠 Trang chủ</a>
                <a href="pages/tra-cuu-dat-phong.php">🔍 Tra cứu đặt phòng</a>
                <a href="#rooms">🛏️ Xem phòng</a>
                <a href="#contact">📞 Liên hệ</a>
            </nav>
        </header>

        <main class="booking-main" id="rooms">
            <!-- Bộ lọc loại phòng -->
            <section class="filter-section">
                <h2>Chọn loại phòng</h2>
                <div class="room-types">
                    <a href="index.php" class="room-type-card <?= !$loaiPhongFilter ? 'active' : '' ?>">
                        <div class="card-icon">🏠</div>
                        <h3>Tất cả</h3>
                        <p><?= count($phongController->traCuuPhong(null, 'Trống')) ?> phòng</p>
                    </a>
                    <?php foreach ($loaiPhongs as $loai): 
                        $soPhong = count($phongController->traCuuPhong($loai['MaLoaiPhong'], 'Trống'));
                    ?>
                    <a href="index.php?loai=<?= $loai['MaLoaiPhong'] ?>#rooms" 
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
                    <a href="index.php" class="btn">Xem tất cả phòng</a>
                </div>
                <?php endif; ?>
            </section>

            <!-- Thông tin thêm -->
            <section class="info-section" id="contact">
                <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Thông tin dịch vụ</h2>
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
                    <div class="info-card">
                        <div class="info-icon">🍽️</div>
                        <h3>Nhà hàng</h3>
                        <p>6:00 - 22:00</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">🏊</div>
                        <h3>Bể bơi</h3>
                        <p>5:00 - 21:00</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">🚗</div>
                        <h3>Đậu xe</h3>
                        <p>Miễn phí</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">📶</div>
                        <h3>WiFi</h3>
                        <p>Miễn phí</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="booking-footer">
            <p>&copy; 2024 Khách sạn - Hệ thống đặt phòng trực tuyến</p>
            <p style="margin-top: 10px; font-size: 0.9em;">
                📍 Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM | 
                📞 Hotline: 1900-xxxx | 
                📧 Email: contact@hotel.com
            </p>
        </footer>
    </div>
</body>
</html>
