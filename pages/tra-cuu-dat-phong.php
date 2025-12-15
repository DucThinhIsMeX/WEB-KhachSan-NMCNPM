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
    <link rel="stylesheet" href="../assets/css/booking.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .search-hero {
            background: linear-gradient(135deg, rgba(102,126,234,0.95) 0%, rgba(118,75,162,0.95) 100%),
                        url('https://hotelroyalhoian.vn/wp-content/uploads/2025/05/dac-san-hoi-an-1-1.jpg');
            background-size: cover;
            background-position: center;
            padding: 60px 20px;
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .search-hero h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .search-hero p {
            font-size: 1.2em;
            opacity: 0.95;
        }
        
        .search-container {
            max-width: 800px;
            margin: -30px auto 40px;
            position: relative;
            z-index: 10;
        }
        
        .search-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .search-card h2 {
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        
        .search-form .form-group {
            flex: 1;
        }
        
        .search-form input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1em;
            transition: 0.3s;
        }
        
        .search-form input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .search-form .btn {
            padding: 15px 35px;
            white-space: nowrap;
            border-radius: 12px;
        }
        
        .results-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .results-header h2 {
            color: var(--dark);
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .results-count {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .result-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
            border-left: 5px solid var(--primary);
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .result-header h3 {
            color: var(--primary);
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-completed {
            background: #cce5ff;
            color: #004085;
        }
        
        .result-body {
            display: grid;
            gap: 15px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .info-row i {
            color: var(--primary);
            font-size: 1.3em;
        }
        
        .info-row strong {
            color: var(--dark);
            min-width: 120px;
        }
        
        .guest-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
        }
        
        .guest-list h4 {
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .guest-item {
            padding: 10px;
            background: white;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .guest-item:last-child {
            margin-bottom: 0;
        }
        
        .guest-type {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .guest-local {
            background: #d4edda;
            color: #155724;
        }
        
        .guest-foreign {
            background: #fff3cd;
            color: #856404;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            margin: 40px 0;
        }
        
        .empty-state i {
            font-size: 5em;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--dark);
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 1.1em;
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .results-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: center;
            }
            
            .result-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Search Hero -->
    <div class="search-hero">
        <h1><i class="ph ph-magnifying-glass"></i> Tra Cứu Đặt Phòng</h1>
        <p>Kiểm tra thông tin đặt phòng của bạn một cách nhanh chóng</p>
    </div>

    <!-- Navigation -->
    <nav class="booking-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.php"><i class="ph ph-house"></i> Trang chủ</a>
                <a href="tra-cuu-dat-phong.php" class="active"><i class="ph ph-magnifying-glass"></i> Tra cứu</a>
            </div>
            <a href="../admin/login.php" class="nav-link nav-login">
                <i class="ph ph-user-circle"></i>
                <span>Đăng Nhập</span>
            </a>
        </div>
    </nav>

    <!-- Search Form -->
    <div class="search-container">
        <div class="search-card">
            <h2><i class="ph ph-funnel"></i> Nhập Thông Tin Tra Cứu</h2>
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label>Mã phiếu thuê, CMND hoặc Tên khách hàng:</label>
                    <input type="text" name="keyword" placeholder="Nhập mã phiếu, CMND hoặc tên..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
                </div>
                <button type="submit" name="search" class="btn btn-primary">
                    <i class="ph ph-magnifying-glass"></i> Tìm Kiếm
                </button>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="results-section">
        <div class="alert alert-danger" style="background: #fee; border-left: 4px solid #f00; padding: 20px; border-radius: 12px;">
            <i class="ph ph-warning"></i> <?= htmlspecialchars($error) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($ketQua !== null): ?>
    <div class="results-section">
        <div class="results-header">
            <h2>
                <i class="ph ph-list-bullets"></i> Kết Quả Tìm Kiếm
                <span class="results-count"><?= count($ketQua) ?> phiếu</span>
            </h2>
            <div class="action-buttons">
                <button type="button" id="exportBtn" class="btn btn-primary">
                    <i class="ph ph-download-simple"></i> Export JSON
                </button>
                <button type="button" id="printBtn" class="btn btn-primary">
                    <i class="ph ph-printer"></i> In
                </button>
            </div>
        </div>
        
        <?php if (count($ketQua) > 0): ?>
            <?php foreach ($ketQua as $pt): 
                try {
                    $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                } catch (Exception $e) {
                    $khachs = [];
                }
                $statusClass = $pt['TinhTrangPhieu'] === 'Đang thuê' ? 'status-active' : 'status-completed';
            ?>
            <div class="result-card">
                <div class="result-header">
                    <h3><i class="ph ph-ticket"></i> Phiếu Thuê #<?= htmlspecialchars($pt['MaPhieuThue']) ?></h3>
                    <span class="status-badge <?= $statusClass ?>">
                        <?= htmlspecialchars($pt['TinhTrangPhieu']) ?>
                    </span>
                </div>
                
                <div class="result-body">
                    <div class="info-row">
                        <i class="ph ph-bed"></i>
                        <strong>Phòng:</strong>
                        <span><?= htmlspecialchars($pt['SoPhong']) ?> - <?= htmlspecialchars($pt['TenLoai']) ?></span>
                    </div>
                    <div class="info-row">
                        <i class="ph ph-calendar"></i>
                        <strong>Ngày thuê:</strong>
                        <span><?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?></span>
                    </div>
                    
                    <?php if (count($khachs) > 0): ?>
                    <div class="guest-list">
                        <h4><i class="ph ph-users"></i> Danh sách khách (<?= count($khachs) ?> người)</h4>
                        <?php foreach ($khachs as $k): ?>
                        <div class="guest-item">
                            <i class="ph ph-user"></i>
                            <strong><?= htmlspecialchars($k['TenKhach']) ?></strong>
                            <span class="guest-type <?= $k['LoaiKhach'] === 'Nội địa' ? 'guest-local' : 'guest-foreign' ?>">
                                <?= htmlspecialchars($k['LoaiKhach']) ?>
                            </span>
                            <span style="color: #666;">
                                <i class="ph ph-identification-card"></i> <?= htmlspecialchars($k['CMND']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="ph ph-magnifying-glass"></i>
                <h3>Không Tìm Thấy Kết Quả</h3>
                <p>Không tìm thấy thông tin đặt phòng phù hợp. Vui lòng kiểm tra lại thông tin tìm kiếm.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        const printBtn = document.getElementById('printBtn');
        const exportBtn = document.getElementById('exportBtn');
        
        printBtn && printBtn.addEventListener('click', function() { 
            window.print(); 
        });

        exportBtn && exportBtn.addEventListener('click', function(){
            const cards = Array.from(document.querySelectorAll('.result-card'));
            const data = cards.map(card => ({
                title: card.querySelector('h3') ? card.querySelector('h3').innerText.trim() : '',
                info: card.querySelector('.result-body') ? card.querySelector('.result-body').innerText.trim() : ''
            }));
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; 
            a.download = 'tim_kiem_phieu_thue_' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a); 
            a.click(); 
            a.remove();
        });
    </script>
    <?php endif; ?>

    <footer class="booking-footer" style="margin-top: 60px;">
        <p>&copy; 2024 Khách sạn - Hệ thống đặt phòng trực tuyến</p>
    </footer>
</body>
</html>

