 
<?php
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../config/database.php';

$phieuThueCtrl = new PhieuThueController();
$database = new Database();
$db = $database->connect();

$ketQua = null;
$error = null;

if (isset($_GET['search']) && !empty($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    
    try {
        // Tìm kiếm theo mã phiếu hoặc CMND
        $stmt = $db->prepare("
            SELECT DISTINCT PT.*, P.SoPhong, L.TenLoai 
            FROM PHIEUTHUE PT
            JOIN PHONG P ON PT.MaPhong = P.MaPhong
            JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong
            LEFT JOIN CHITIET_THUE CT ON PT.MaPhieuThue = CT.MaPhieuThue
            LEFT JOIN KHACHHANG K ON CT.MaKhachHang = K.MaKhachHang
            WHERE PT.MaPhieuThue = ? 
               OR K.CMND = ? 
               OR K.TenKhach LIKE ?
            ORDER BY PT.NgayBatDauThue DESC
        ");
        $stmt->execute([$keyword, $keyword, "%$keyword%"]);
        $ketQua = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = "Lỗi khi tìm kiếm: " . $e->getMessage();
        $ketQua = [];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra Cứu Đặt Phòng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>🔍 Tra Cứu Đặt Phòng</h1>
                <p>Kiểm tra thông tin đặt phòng của bạn</p>
            </div>
            <nav class="booking-nav">
                <a href="../index.php">🏠 Trang chủ</a>
                <a href="tra-cuu-dat-phong.php" class="active">🔍 Tra cứu đặt phòng</a>
            </nav>
        </header>

        <main class="booking-main">
            <section class="filter-section">
                <h2>Nhập Thông Tin Tra Cứu</h2>
                <form method="GET">
                    <div class="form-group">
                        <label>Mã phiếu thuê, CMND hoặc Tên khách hàng:</label>
                        <input type="text" name="keyword" placeholder="Nhập để tìm kiếm..." 
                               value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
                    </div>
                    <button type="submit" name="search" class="btn">🔍 Tìm Kiếm</button>
                </form>
                
                <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-top: 15px; padding: 15px; background: #fee; border-left: 4px solid #f00; border-radius: 4px;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
            </section>

            <?php if ($ketQua !== null): ?>
            <section class="rooms-section">
                <h2>📋 Kết Quả Tìm Kiếm (<?= count($ketQua) ?> phiếu)</h2>
                
                <?php if (count($ketQua) > 0): ?>
                    <?php foreach ($ketQua as $pt): 
                        try {
                            $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                        } catch (Exception $e) {
                            $khachs = [];
                        }
                    ?>
                    <div class="room-card">
                        <div class="room-details">
                            <h3>Phiếu Thuê #<?= htmlspecialchars($pt['MaPhieuThue']) ?></h3>
                            <div class="room-info">
                                <p>🛏️ Phòng: <strong><?= htmlspecialchars($pt['SoPhong']) ?> - <?= htmlspecialchars($pt['TenLoai']) ?></strong></p>
                                <p>📅 Ngày thuê: <strong><?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?></strong></p>
                                <p>📊 Tình trạng: <strong><?= htmlspecialchars($pt['TinhTrangPhieu']) ?></strong></p>
                                <?php if (count($khachs) > 0): ?>
                                <p>👥 Danh sách khách:</p>
                                <ul>
                                    <?php foreach ($khachs as $k): ?>
                                    <li><?= htmlspecialchars($k['TenKhach']) ?> (<?= htmlspecialchars($k['LoaiKhach']) ?>) - CMND: <?= htmlspecialchars($k['CMND']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-rooms">
                        <p>😔 Không tìm thấy thông tin đặt phòng</p>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </main>

        <footer class="booking-footer">
            <p>&copy; 2024 Khách sạn - Hệ thống đặt phòng trực tuyến</p>
        </footer>
    </div>
</body>
</html>
 
<?php
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../config/database.php';

$phieuThueCtrl = new PhieuThueController();
$database = new Database();
$db = $database->connect();

$ketQua = null;
$error = null;

if (isset($_GET['search']) && !empty($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    
    try {
        // Tìm kiếm theo mã phiếu hoặc CMND
        $stmt = $db->prepare("
            SELECT DISTINCT PT.*, P.SoPhong, L.TenLoai 
            FROM PHIEUTHUE PT
            JOIN PHONG P ON PT.MaPhong = P.MaPhong
            JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong
            LEFT JOIN CHITIET_THUE CT ON PT.MaPhieuThue = CT.MaPhieuThue
            LEFT JOIN KHACHHANG K ON CT.MaKhachHang = K.MaKhachHang
            WHERE PT.MaPhieuThue = ? 
               OR K.CMND = ? 
               OR K.TenKhach LIKE ?
            ORDER BY PT.NgayBatDauThue DESC
        ");
        $stmt->execute([$keyword, $keyword, "%$keyword%"]);
        $ketQua = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = "Lỗi khi tìm kiếm: " . $e->getMessage();
        $ketQua = [];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra Cứu Đặt Phòng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>🔍 Tra Cứu Đặt Phòng</h1>
                <p>Kiểm tra thông tin đặt phòng của bạn</p>
            </div>
            <nav class="booking-nav">
                <a href="../index.php">🏠 Trang chủ</a>
                <a href="tra-cuu-dat-phong.php" class="active">🔍 Tra cứu đặt phòng</a>
            </nav>
        </header>

        <main class="booking-main">
            <section class="filter-section">
                <h2>Nhập Thông Tin Tra Cứu</h2>
                <form method="GET">
                    <div class="form-group">
                        <label>Mã phiếu thuê, CMND hoặc Tên khách hàng:</label>
                        <input type="text" name="keyword" placeholder="Nhập để tìm kiếm..." 
                               value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
                    </div>
                    <button type="submit" name="search" class="btn">🔍 Tìm Kiếm</button>
                    <button type="button" id="exportBtn" class="export-btn" style="margin-left: 10px;">📤 Export JSON</button>
                    <button type="button" id="printBtn" class="btn-ghost" style="margin-left: 10px;">🖨️ In</button>
                </form>
                
                <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-top: 15px; padding: 15px; background: #fee; border-left: 4px solid #f00; border-radius: 4px;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
            </section>

            <?php if ($ketQua !== null): ?>
            <section class="rooms-section">
                <h2>📋 Kết Quả Tìm Kiếm (<?= count($ketQua) ?> phiếu)</h2>
                
                <?php if (count($ketQua) > 0): ?>
                    <?php foreach ($ketQua as $pt): 
                        try {
                            $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                        } catch (Exception $e) {
                            $khachs = [];
                        }
                    ?>
                    <div class="room-card">
                        <div class="room-details">
                            <h3>Phiếu Thuê #<?= htmlspecialchars($pt['MaPhieuThue']) ?></h3>
                            <div class="room-info">
                                <p>🛏️ Phòng: <strong><?= htmlspecialchars($pt['SoPhong']) ?> - <?= htmlspecialchars($pt['TenLoai']) ?></strong></p>
                                <p>📅 Ngày thuê: <strong><?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?></strong></p>
                                <p>📊 Tình trạng: <strong><?= htmlspecialchars($pt['TinhTrangPhieu']) ?></strong></p>
                                <?php if (count($khachs) > 0): ?>
                                <p>👥 Danh sách khách:</p>
                                <ul>
                                    <?php foreach ($khachs as $k): ?>
                                    <li><?= htmlspecialchars($k['TenKhach']) ?> (<?= htmlspecialchars($k['LoaiKhach']) ?>) - CMND: <?= htmlspecialchars($k['CMND']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-rooms">
                        <p>😔 Không tìm thấy thông tin đặt phòng</p>
                    </div>
                <?php endif; ?>
            </section>
            <script>
                const printBtn = document.getElementById('printBtn');
                const exportBtn = document.getElementById('exportBtn');
                printBtn && printBtn.addEventListener('click', function() { window.print(); });

                exportBtn && exportBtn.addEventListener('click', function(){
                    // Gather results from DOM
                    const cards = Array.from(document.querySelectorAll('.rooms-section .room-card'));
                    const data = cards.map(card => {
                        return {
                            title: card.querySelector('h3') ? card.querySelector('h3').innerText.trim() : '',
                            info: card.querySelector('.room-info') ? card.querySelector('.room-info').innerText.trim() : ''
                        }
                    });
                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url; a.download = 'tim_kiem_phieu_thue.json';
                    document.body.appendChild(a); a.click(); a.remove();
                });
            </script>
            <?php endif; ?>
        </main>

        <footer class="booking-footer">
            <p>&copy; 2024 Khách sạn - Hệ thống đặt phòng trực tuyến</p>
        </footer>
    </div>
</body>
</html>
 
