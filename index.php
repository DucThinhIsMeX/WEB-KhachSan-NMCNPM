<?php
require_once 'config/database.php';
require_once 'controllers/PhongController.php';

$database = new Database();

if (!$database->isDatabaseInitialized()) {
    header('Location: database/init.php');
    exit;
}

$db = $database->connect();

try {
    $db->query("SELECT 1 FROM LOAIPHONG LIMIT 1");
} catch(PDOException $e) {
    header('Location: database/init.php');
    exit;
}

$phongController = new PhongController();
$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG ORDER BY DonGiaCoBan")->fetchAll();
$loaiPhongFilter = isset($_GET['loai']) ? $_GET['loai'] : null;
$searchQ = isset($_GET['q']) ? trim($_GET['q']) : null;
$phongsTrong = $phongController->traCuuPhong($loaiPhongFilter, 'Trống');

// Server-side filtering by search query (SoPhong or TenLoai)
if ($searchQ) {
    $searchQ = strtolower($searchQ);
    $phongsTrong = array_values(array_filter($phongsTrong, function($p) use ($searchQ) {
        return stripos($p['SoPhong'] . ' ' . $p['TenLoai'], $searchQ) !== false;
    }));
}

// Gallery images per room type (TenLoai). Add images here or update to pull from DB.
// Gallery images per room type (TenLoai). Add images here or update to pull from DB.
$galleryImagesByType = [
    // Loại A gallery removed: keep empty so no gallery renders for Loại A
    'Loại A' => [],
    'Loại B' => [],
    'Loại C' => [],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Phòng Khách Sạn - Hotel Management System</title>
    <link rel="stylesheet" href="assets/css/booking.css">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>🏨 Khách Sạn Sang Trọng</h1>
            <p class="subtitle">Trải nghiệm nghỉ dưỡng đẳng cấp - Đặt phòng dễ dàng chỉ trong vài phút</p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <h3><?= count($loaiPhongs) ?></h3>
                    <p>Loại Phòng</p>
                </div>
                <div class="hero-stat">
                    <h3><?= count($phongsTrong) ?></h3>
                    <p>Phòng Trống</p>
                </div>
                <div class="hero-stat">
                    <h3>24/7</h3>
                    <p>Hỗ Trợ</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation -->
    <nav class="booking-nav">
        <div class="nav-container">
            <a href="index.php" class="nav-link active">
                <span>🏠</span>
                <span>Trang Chủ</span>
            </a>
            <a href="pages/tra-cuu-dat-phong.php" class="nav-link">
                <span>🔍</span>
                <span>Tra Cứu Đặt Phòng</span>
            </a>
            <a href="admin/login.php" class="nav-link">
                <span>🔐</span>
                <span>Đăng Nhập Admin</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Filter Section -->
        <section class="filter-section">
            <h2>📋 Chọn Loại Phòng</h2>
            <form method="GET" class="search-bar" role="search">
                <input type="hidden" name="loai" value="<?= htmlspecialchars($loaiPhongFilter ?? '') ?>">
                <input type="text" name="q" class="search-input" placeholder="Tìm phòng theo số phòng hoặc loại..." value="<?= htmlspecialchars($searchQ ?? '') ?>">
                <button type="submit" class="btn-primary search-btn">🔎 Tìm</button>
            </form>
            <div class="filter-grid">
                <a href="index.php" class="filter-card filter-card--all <?= !$loaiPhongFilter ? 'active' : '' ?>">
                    <h3>Tất Cả Phòng</h3>
                    <div class="filter-count"><?= count($phongController->traCuuPhong(null, 'Trống')) ?> phòng có sẵn</div>
                </a>
                
                <?php foreach ($loaiPhongs as $loai): 
                    $soPhong = count($phongController->traCuuPhong($loai['MaLoaiPhong'], 'Trống'));
                    // Remove decorative icons (sofa/bed/crown) to simplify the UI
                    $icon = '';
                    $filterTypeClass = $loai['TenLoai'] == 'Loại A' ? 'filter-card--type-a' : ($loai['TenLoai'] == 'Loại B' ? 'filter-card--type-b' : 'filter-card--type-c');
                    $filterCardClass = 'filter-card ' . $filterTypeClass . ' ' . ($loaiPhongFilter == $loai['MaLoaiPhong'] ? 'active' : '');
                ?>
                <a href="index.php?loai=<?= $loai['MaLoaiPhong'] ?>" 
                   class="<?= htmlspecialchars($filterCardClass) ?>">
                    <div class="filter-icon"><?= $icon ?></div>
                    <h3><?= $loai['TenLoai'] ?></h3>
                    <div class="filter-price"><?= number_format($loai['DonGiaCoBan']) ?>đ/đêm</div>
                    <div class="filter-count"><?= $soPhong ?> phòng có sẵn</div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php
            // Render a small gallery when a specific room type is selected
            if ($loaiPhongFilter) {
                // Find the TenLoai by MaLoaiPhong
                $selectedLoai = null;
                foreach ($loaiPhongs as $l) {
                    if ($l['MaLoaiPhong'] == $loaiPhongFilter) { $selectedLoai = $l; break; }
                }
                if ($selectedLoai !== null && isset($galleryImagesByType[$selectedLoai['TenLoai']]) && count($galleryImagesByType[$selectedLoai['TenLoai']]) > 0) {
                    $images = $galleryImagesByType[$selectedLoai['TenLoai']];
            ?>
            <div class="type-gallery" aria-live="polite">
                <h3>Hình ảnh Loại: <?= htmlspecialchars($selectedLoai['TenLoai']) ?></h3>
                <div class="type-gallery-grid">
                    <?php foreach ($images as $img): ?>
                        <div class="type-gallery-item">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($selectedLoai['TenLoai']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </section>

        <!-- Rooms Grid -->
        <?php if (count($phongsTrong) > 0): ?>
        <div class="rooms-grid">
            <?php foreach ($phongsTrong as $phong): 
                    $roomType = $phong['TenLoai'];
                    // Remove decorative icons in room headers for clear layout
                    $icon = '';
                    if ($roomType == 'Loại A') {
                        $headerClass = 'room-header room-header--type-a';
                    } elseif ($roomType == 'Loại B') {
                        $headerClass = 'room-header room-header--type-b';
                    } else {
                        $headerClass = 'room-header room-header--type-c';
                    }
                ?>
            <div class="room-card">
                <div class="<?= htmlspecialchars($headerClass) ?>">
                    <?php if (!empty($icon)): ?>
                        <div class="room-icon"><?= $icon ?></div>
                    <?php endif; ?>
                    <div class="room-number">Phòng <?= htmlspecialchars($phong['SoPhong']) ?></div>
                </div>
                
                <div class="room-body">
                    <h3 class="room-title"><?= $phong['TenLoai'] ?></h3>
                    
                    <div class="room-features">
                        <div class="feature-item">
                            <span class="feature-icon">📍</span>
                            <span class="feature-label">Số phòng</span>
                            <span class="feature-value"><?= $phong['SoPhong'] ?></span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">👥</span>
                            <span class="feature-label">Sức chứa</span>
                            <span class="feature-value">Tối đa <?= $soKhachToiDa ?> khách</span>
                        </div>
                        <?php if ($phong['GhiChu']): ?>
                        <div class="feature-item">
                            <span class="feature-icon">📝</span>
                            <span class="feature-label">Ghi chú</span>
                            <span class="feature-value"><?= $phong['GhiChu'] ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="room-price">
                        <div class="price-label">Giá phòng</div>
                        <div class="price-value">
                            <?= number_format($phong['DonGiaCoBan']) ?>
                            <span class="price-unit">VNĐ/đêm</span>
                        </div>
                    </div>
                    
                    <a href="pages/form-dat-phong.php?phong=<?= $phong['MaPhong'] ?>" class="btn-book">
                        <span>📝</span>
                        <span>Đặt Phòng Ngay</span>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">😔</div>
            <h3>Không Có Phòng Trống</h3>
            <p>Hiện tại không có phòng trống trong loại này. Vui lòng chọn loại phòng khác.</p>
            <a href="index.php" class="btn btn-primary">Xem Tất Cả Phòng</a>
        </div>
        <?php endif; ?>

        <!-- Info Section -->
        <section class="info-section">
            <h2>🎯 Dịch Vụ & Tiện Ích</h2>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">⏰</div>
                    <h3>Nhận Phòng</h3>
                    <p>Từ 14:00</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🚪</div>
                    <h3>Trả Phòng</h3>
                    <p>Trước 12:00</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">💳</div>
                    <h3>Thanh Toán</h3>
                    <p>Tiền mặt, Thẻ</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">📞</div>
                    <h3>Hotline 24/7</h3>
                    <p>1900-xxxx</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🍽️</div>
                    <h3>Nhà Hàng</h3>
                    <p>6:00 - 22:00</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🏊</div>
                    <h3>Bể Bơi</h3>
                    <p>5:00 - 21:00</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🚗</div>
                    <h3>Bãi Đậu Xe</h3>
                    <p>Miễn phí</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">📶</div>
                    <h3>WiFi</h3>
                    <p>Tốc độ cao</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="booking-footer">
        <div class="footer-content">
            <p style="font-size: 1.2em; margin-bottom: 15px;">&copy; 2024 Khách Sạn Sang Trọng - Hotel Management System</p>
            <div class="footer-links">
                <a href="#" class="footer-link">📍 123 Đường ABC, Quận XYZ, TP.HCM</a>
                <a href="#" class="footer-link">📞 Hotline: 1900-xxxx</a>
                <a href="#" class="footer-link">📧 Email: contact@hotel.com</a>
            </div>
        </div>
    </footer>
</body>
</html>
