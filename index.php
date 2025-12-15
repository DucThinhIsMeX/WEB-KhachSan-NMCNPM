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

// Server-side filtering by search query
if ($searchQ) {
    $searchQ = strtolower($searchQ);
    $phongsTrong = array_values(array_filter($phongsTrong, function($p) use ($searchQ) {
        return stripos($p['SoPhong'] . ' ' . $p['TenLoai'], $searchQ) !== false;
    }));
}

// Gallery images per room type
$galleryImagesByType = [
    'Loại A' => [
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/781598207.jpg?k=85857d4624ac244979f0a05ce375c7fca3aed3f38432f14c6a5e2fe88fced8c5&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/781598204.jpg?k=b4bcd7b8a45ba2854f83e8787881695aafd1fdc4a9246a8161a96ef3e04f85c3&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/781598239.jpg?k=cdde2a8650e8d9473d46a9c970b6812edf4f60ac7bdbb54136fb250ec1b8c523&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/781598352.jpg?k=266b179529c81d14abd5476e116408b86d73d1dcb1ffe3d870073ebf899f8edc&o='
    ],
    'Loại B' => [
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/618207154.jpg?k=0b16b6e8e0e359353538fe56facd1b5bf36c8119944b78c3c208c8bcdf855748&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/609527454.jpg?k=dbf0b5373264fa2141ca059a991af078eaa3b98762f7f14250458d33d9d45ca8&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/609529875.jpg?k=6efb01fca1b8674787083f94c4386a82b36f4ed368cb395566a7327d2a372555&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/609533794.jpg?k=20f29f6863117b2c3bb85f6af380bf64df2fcb6d83f33e3be35630c2b3e50c96&o='
    ],
    'Loại C' => [
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/786166480.jpg?k=b8295a6c79a796daa1a8c86841fa21da528771d16b4a3ee3acdb47716c441003&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/786165449.jpg?k=e77157cacbfa611407adaf4f455d96f60be8fe32c8d4f4c6a0a63e3b3fd8916f&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/786166591.jpg?k=457155977656700b042551cf6661fad7e28dee7499e3218571a2e28c110a956c&o=',
        'https://cf.bstatic.com/xdata/images/hotel/max1024x768/786237066.jpg?k=4f8a2cf5b0d0c1e07789a993cc2bbb25bb5235825480d6ab753cf23010a654ef&o='
    ],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Phòng Khách Sạn - Hotel Management System</title>
    <link rel="stylesheet" href="assets/css/booking.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1><i class="ph ph-buildings"></i> Khách Sạn Sang Trọng</h1>
            <p class="subtitle">Trải nghiệm nghỉ dưỡng đẳng cấp - Đặt phòng dễ dàng chỉ trong vài phút</p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <h3><?= count($loaiPhongs) ?></h3>
                    <p><i class="ph ph-tag"></i> Loại Phòng</p>
                </div>
                <div class="hero-stat">
                    <h3><?= count($phongsTrong) ?></h3>
                    <p><i class="ph ph-sparkle"></i> Phòng Trống</p>
                </div>
                <div class="hero-stat">
                    <h3>24/7</h3>
                    <p><i class="ph ph-headset"></i> Hỗ Trợ</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation -->
    <nav class="booking-nav">
        <div class="nav-container">
            <a href="index.php" class="nav-link active">
                <i class="ph ph-house"></i>
                <span>Trang Chủ</span>
            </a>
            <a href="pages/tra-cuu-dat-phong.php" class="nav-link">
                <i class="ph ph-magnifying-glass"></i>
                <span>Tra Cứu Đặt Phòng</span>
            </a>
            <a href="admin/login.php" class="nav-link">
                <i class="ph ph-lock-key"></i>
                <span>Đăng Nhập Admin</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Filter Section -->
        <section class="filter-section">
            <h2><i class="ph ph-list-bullets"></i> Chọn Loại Phòng</h2>
            <form method="GET" class="search-bar" role="search">
                <input type="hidden" name="loai" value="<?= htmlspecialchars($loaiPhongFilter ?? '') ?>">
                <input type="text" name="q" class="search-input" placeholder="Tìm phòng theo số phòng hoặc loại..." value="<?= htmlspecialchars($searchQ ?? '') ?>">
                <button type="submit" class="btn-primary search-btn">
                    <i class="ph ph-magnifying-glass"></i> Tìm
                </button>
            </form>
            <div class="filter-grid">
                <a href="index.php" class="filter-card filter-card--all <?= !$loaiPhongFilter ? 'active' : '' ?>">
                    <h3>Tất Cả Phòng</h3>
                    <div class="filter-count"><?= count($phongController->traCuuPhong(null, 'Trống')) ?> phòng có sẵn</div>
                </a>
                
                <?php foreach ($loaiPhongs as $loai): 
                    $soPhong = count($phongController->traCuuPhong($loai['MaLoaiPhong'], 'Trống'));
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
                $selectedLoai = null;
                foreach ($loaiPhongs as $l) {
                    if ($l['MaLoaiPhong'] == $loaiPhongFilter) { $selectedLoai = $l; break; }
                }
                if ($selectedLoai !== null && isset($galleryImagesByType[$selectedLoai['TenLoai']]) && count($galleryImagesByType[$selectedLoai['TenLoai']]) > 0) {
                    $images = $galleryImagesByType[$selectedLoai['TenLoai']];
            ?>
            <div class="type-gallery" aria-live="polite" data-images='<?= htmlspecialchars(json_encode($images), ENT_NOQUOTES, 'UTF-8') ?>'>
                <h3>Hình ảnh Loại: <?= htmlspecialchars($selectedLoai['TenLoai']) ?></h3>
                <?php if (count($images) > 0): ?>
                <div class="type-gallery-main" tabindex="0">
                    <img src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($selectedLoai['TenLoai']) ?>" data-index="0">
                </div>
                <?php endif; ?>
                <!-- thumbnails intentionally hidden; use modal to view other images -->
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
                            <i class="ph ph-map-pin feature-icon"></i>
                            <span class="feature-label">Số phòng</span>
                            <span class="feature-value"><?= $phong['SoPhong'] ?></span>
                        </div>
                        <div class="feature-item">
                            <i class="ph ph-users feature-icon"></i>
                            <span class="feature-label">Sức chứa</span>
                            <span class="feature-value">Tối đa <?= $soKhachToiDa ?> khách</span>
                        </div>
                        <?php if ($phong['GhiChu']): ?>
                        <div class="feature-item">
                            <i class="ph ph-note-pencil feature-icon"></i>
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
                        <i class="ph ph-calendar-check"></i>
                        <span>Đặt Phòng Ngay</span>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="ph ph-smiley-sad empty-icon"></i>
            <h3>Không Có Phòng Trống</h3>
            <p>Hiện tại không có phòng trống trong loại này. Vui lòng chọn loại phòng khác.</p>
            <a href="index.php" class="btn btn-primary">Xem Tất Cả Phòng</a>
        </div>
        <?php endif; ?>

        <!-- Info Section -->
        <section class="info-section">
            <h2><i class="ph ph-sparkle"></i> Dịch Vụ & Tiện Ích</h2>
            <div class="info-grid">
                <div class="info-card">
                    <i class="ph ph-clock-afternoon info-icon"></i>
                    <h3>Nhận Phòng</h3>
                    <p>Từ 14:00</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-clock info-icon"></i>
                    <h3>Trả Phòng</h3>
                    <p>Trước 12:00</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-credit-card info-icon"></i>
                    <h3>Thanh Toán</h3>
                    <p>Tiền mặt, Thẻ</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-phone info-icon"></i>
                    <h3>Hotline 24/7</h3>
                    <p>1900-xxxx</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-fork-knife info-icon"></i>
                    <h3>Nhà Hàng</h3>
                    <p>6:00 - 22:00</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-swimming-pool info-icon"></i>
                    <h3>Bể Bơi</h3>
                    <p>5:00 - 21:00</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-car info-icon"></i>
                    <h3>Bãi Đậu Xe</h3>
                    <p>Miễn phí</p>
                </div>
                <div class="info-card">
                    <i class="ph ph-wifi-high info-icon"></i>
                    <h3>WiFi</h3>
                    <p>Tốc độ cao</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="booking-footer">
        <div class="footer-content">
            <p style="font-size: 1.2em; margin-bottom: 15px;">© 2024 Khách Sạn Sang Trọng - Hotel Management System</p>
            <div class="footer-links">
                <a href="#" class="footer-link"><i class="ph ph-map-pin"></i> 123 Đường ABC, Quận XYZ, TP.HCM</a>
                <a href="#" class="footer-link"><i class="ph ph-phone"></i> Hotline: 1900-xxxx</a>
                <a href="#" class="footer-link"><i class="ph ph-envelope"></i> Email: contact@hotel.com</a>
            </div>
        </div>
    </footer>
    <!-- Image Modal -->
    <div id="image-modal" class="image-modal" aria-hidden="true" role="dialog" aria-label="Image viewer">
        <div class="modal-content">
            <button class="modal-close" aria-label="Close">×</button>
            <button class="modal-prev" aria-label="Previous">‹</button>
            <div class="modal-image"><img src="" alt=""></div>
            <button class="modal-next" aria-label="Next">›</button>
        </div>
    </div>
    <script defer src="assets/js/gallery.js"></script>
</body>
</html>

