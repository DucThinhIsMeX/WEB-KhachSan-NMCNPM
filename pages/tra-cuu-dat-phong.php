<?php
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/CustomerAuthController.php';

session_start();
$customerAuth = new CustomerAuthController();
$isCustomerLoggedIn = $customerAuth->isLoggedIn();
$customerInfo = $customerAuth->getCustomerInfo();

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
            SELECT DISTINCT PT.*, P.SoPhong, L.TenLoai, L.DonGiaCoBan
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra Cứu Đặt Phòng</title>
    <link rel="stylesheet" href="../assets/css/booking.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: #f5f7fa;
        }
        
        /* Navigation */
        .booking-nav {
            background: white;
            padding: 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 32px;
            gap: 20px;
        }
        
        .nav-left {
            display: flex;
            gap: 8px;
            padding: 16px 0;
        }
        
        .nav-left a {
            padding: 12px 24px;
            background: #f9fafb;
            color: #6b7280;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95em;
            border: 2px solid transparent;
        }
        
        .nav-left a i {
            font-size: 1.2em;
        }
        
        .nav-left a:hover {
            background: #f3f4f6;
            color: #4f46e5;
            transform: translateY(-1px);
            border-color: #e5e7eb;
        }
        
        .nav-left a.active {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .nav-left a.active:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }
        
        .nav-login {
            padding: 12px 24px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
        }
        
        .nav-login:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }
        
        /* Hero Section */
        .search-hero {
            background: linear-gradient(135deg, rgba(102,126,234,0.95) 0%, rgba(118,75,162,0.95) 100%),
                        url('https://hotelroyalhoian.vn/wp-content/uploads/2025/05/dac-san-hoi-an-1-1.jpg');
            background-size: cover;
            background-position: center;
            padding: 140px 20px 160px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .search-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.1), transparent 70%);
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .search-hero h1 {
            font-size: 3em;
            margin-bottom: 15px;
            font-weight: 700;
            animation: fadeInDown 0.6s ease;
        }
        
        .search-hero .subtitle {
            font-size: 1.3em;
            opacity: 0.95;
            animation: fadeInUp 0.6s ease 0.2s both;
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Search Card */
        .search-container {
            max-width: 900px;
            margin: -80px auto 40px;
            position: relative;
            z-index: 10;
            padding: 0 20px;
        }
        
        .search-card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            animation: fadeInUp 0.6s ease 0.4s both;
        }
        
        .search-card h2 {
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.6em;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        
        .search-form .form-group {
            flex: 1;
        }
        
        .search-form label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .search-form input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1.05em;
            transition: 0.3s;
        }
        
        .search-form input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .search-form .btn {
            padding: 15px 35px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05em;
            cursor: pointer;
            transition: 0.3s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-form .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }
        
        /* Results Section */
        .results-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }
        
        .results-header h2 {
            color: var(--dark);
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .results-count {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1em;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .action-buttons .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #ffd166, #f97316);
            color: white;
        }
        
        .btn-print {
            background: white;
            color: var(--dark);
            border: 2px solid #e9ecef;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Result Card */
        .result-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
            border-left: 6px solid var(--primary);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            font-size: 1.6em;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .status-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 8px;
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
        
        .result-body {
            display: grid;
            gap: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: 0.3s;
        }
        
        .info-row:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .info-row i {
            color: var(--primary);
            font-size: 1.5em;
            flex-shrink: 0;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 4px;
        }
        
        .info-value {
            color: var(--dark);
            font-weight: 600;
            font-size: 1.05em;
        }
        
        /* Guest List */
        .guest-list {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 15px;
            margin-top: 15px;
        }
        
        .guest-list h4 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 1.3em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .guest-item {
            padding: 15px;
            background: white;
            border-radius: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.3s;
        }
        
        .guest-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .guest-item:last-child {
            margin-bottom: 0;
        }
        
        .guest-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2em;
        }
        
        .guest-info {
            flex: 1;
        }
        
        .guest-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .guest-details {
            display: flex;
            gap: 15px;
            color: #666;
            font-size: 0.9em;
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
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            margin: 40px 0;
            animation: fadeIn 0.6s ease;
        }
        
        .empty-state i {
            font-size: 6em;
            color: #ccc;
            margin-bottom: 25px;
            display: block;
        }
        
        .empty-state h3 {
            color: var(--dark);
            font-size: 2em;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 1.15em;
            margin-bottom: 30px;
        }
        
        /* Alert */
        .alert {
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        .alert-danger {
            background: #fee;
            border-left: 4px solid #f00;
            color: #c00;
        }
        
        .alert i {
            font-size: 1.5em;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .search-hero h1 {
                font-size: 2em;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .results-header {
                flex-direction: column;
                gap: 20px;
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
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .guest-details {
                flex-direction: column;
                gap: 8px;
            }
        }
        
        @media print {
            .search-hero, .booking-nav, .action-buttons, .search-card {
                display: none !important;
            }
            
            .result-card {
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="search-hero">
        <div class="hero-content">
            <h1><i class="ph ph-magnifying-glass"></i> Tra Cứu Đặt Phòng</h1>
            <p class="subtitle">Kiểm tra thông tin đặt phòng của bạn một cách nhanh chóng và dễ dàng</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="booking-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.php"><i class="ph ph-house"></i> Trang chủ</a>
                <a href="tra-cuu-dat-phong.php" class="active"><i class="ph ph-magnifying-glass"></i> Tra cứu</a>
            </div>
            <?php if ($isCustomerLoggedIn): ?>
            <div class="nav-user-menu">
                <div class="user-avatar">
                    <img src="<?= htmlspecialchars($customerInfo['avatar']) ?>" alt="<?= htmlspecialchars($customerInfo['name']) ?>">
                    <span><?= htmlspecialchars($customerInfo['name']) ?></span>
                    <i class="ph ph-caret-down"></i>
                </div>
                <div class="user-dropdown">
                    <a href="../customer/profile.php"><i class="ph ph-user"></i> Thông tin cá nhân</a>
                    <a href="../customer/bookings.php"><i class="ph ph-ticket"></i> Lịch sử đặt phòng</a>
                    <hr>
                    <a href="../customer/logout.php"><i class="ph ph-sign-out"></i> Đăng xuất</a>
                </div>
            </div>
            <?php else: ?>
            <a href="../customer/login.php" class="nav-link nav-login">
                <i class="ph ph-user-circle"></i>
                <span>Đăng Nhập</span>
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Search Form -->
    <div class="search-container">
        <div class="search-card">
            <h2><i class="ph ph-funnel"></i> Nhập Thông Tin Tra Cứu</h2>
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label>Mã phiếu thuê, CMND hoặc Tên khách hàng:</label>
                    <input type="text" name="keyword" placeholder="Ví dụ: PT001, 123456789 hoặc Nguyễn Văn A..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
                </div>
                <button type="submit" name="search" class="btn">
                    <i class="ph ph-magnifying-glass"></i> Tìm Kiếm
                </button>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="results-section">
        <div class="alert alert-danger">
            <i class="ph ph-warning"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($ketQua !== null): ?>
    <div class="results-section">
        <div class="results-header">
            <h2>
                <i class="ph ph-list-bullets"></i> Kết Quả Tìm Kiếm
            </h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <span class="results-count"><?= count($ketQua) ?> phiếu</span>
                <div class="action-buttons">
                    <button type="button" id="exportBtn" class="btn btn-export">
                        <i class="ph ph-download-simple"></i> Export JSON
                    </button>
                    <button type="button" id="printBtn" class="btn btn-print">
                        <i class="ph ph-printer"></i> In
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (count($ketQua) > 0): ?>
            <?php foreach ($ketQua as $pt): 
                try {
                    $khachs = $phieuThueCtrl->getChiTietKhach($pt['MaPhieuThue']);
                } catch (Exception $e) {
                    $khachs = [];
                }
                
                $statusClass = '';
                $statusIcon = '';
                switch ($pt['TinhTrangPhieu']) {
                    case 'Đang thuê':
                        $statusClass = 'status-active';
                        $statusIcon = 'ph-clock';
                        break;
                    case 'Đã thanh toán':
                        $statusClass = 'status-completed';
                        $statusIcon = 'ph-check-circle';
                        break;
                    case 'Đã hủy':
                        $statusClass = 'status-cancelled';
                        $statusIcon = 'ph-x-circle';
                        break;
                }
            ?>
            <div class="result-card">
                <div class="result-header">
                    <h3><i class="ph ph-ticket"></i> Phiếu Thuê #<?= htmlspecialchars($pt['MaPhieuThue']) ?></h3>
                    <span class="status-badge <?= $statusClass ?>">
                        <i class="ph <?= $statusIcon ?>"></i>
                        <?= htmlspecialchars($pt['TinhTrangPhieu']) ?>
                    </span>
                </div>
                
                <div class="result-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <i class="ph ph-bed"></i>
                            <div class="info-content">
                                <div class="info-label">Phòng</div>
                                <div class="info-value"><?= htmlspecialchars($pt['SoPhong']) ?> - <?= htmlspecialchars($pt['TenLoai']) ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="ph ph-calendar"></i>
                            <div class="info-content">
                                <div class="info-label">Ngày thuê</div>
                                <div class="info-value"><?= date('d/m/Y', strtotime($pt['NgayBatDauThue'])) ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="ph ph-currency-circle-dollar"></i>
                            <div class="info-content">
                                <div class="info-label">Đơn giá</div>
                                <div class="info-value"><?= number_format($pt['DonGiaCoBan']) ?>đ/đêm</div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($khachs) > 0): ?>
                    <div class="guest-list">
                        <h4><i class="ph ph-users"></i> Danh sách khách (<?= count($khachs) ?> người)</h4>
                        <?php foreach ($khachs as $index => $k): ?>
                        <div class="guest-item">
                            <div class="guest-avatar"><?= strtoupper(substr($k['TenKhach'], 0, 1)) ?></div>
                            <div class="guest-info">
                                <div class="guest-name"><?= htmlspecialchars($k['TenKhach']) ?></div>
                                <div class="guest-details">
                                    <span class="guest-type <?= $k['LoaiKhach'] === 'Nội địa' ? 'guest-local' : 'guest-foreign' ?>">
                                        <?= htmlspecialchars($k['LoaiKhach']) ?>
                                    </span>
                                    <span><i class="ph ph-identification-card"></i> <?= htmlspecialchars($k['CMND']) ?></span>
                                </div>
                            </div>
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
                <p>Không tìm thấy thông tin đặt phòng phù hợp với từ khóa "<?= htmlspecialchars($_GET['keyword']) ?>". <br>Vui lòng kiểm tra lại thông tin tìm kiếm.</p>
                <a href="tra-cuu-dat-phong.php" class="btn btn-primary">
                    <i class="ph ph-arrow-counter-clockwise"></i> Thử lại
                </a>
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
                maPhieu: card.querySelector('h3') ? card.querySelector('h3').innerText.trim() : '',
                phong: card.querySelector('.info-value') ? card.querySelector('.info-value').innerText.trim() : '',
                ngayThue: card.querySelectorAll('.info-value')[1] ? card.querySelectorAll('.info-value')[1].innerText.trim() : '',
                donGia: card.querySelectorAll('.info-value')[2] ? card.querySelectorAll('.info-value')[2].innerText.trim() : '',
                tinhTrang: card.querySelector('.status-badge') ? card.querySelector('.status-badge').innerText.trim() : '',
                danhSachKhach: Array.from(card.querySelectorAll('.guest-item')).map(g => ({
                    ten: g.querySelector('.guest-name') ? g.querySelector('.guest-name').innerText.trim() : '',
                    loai: g.querySelector('.guest-type') ? g.querySelector('.guest-type').innerText.trim() : ''
                }))
            }));
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; 
            a.download = 'tra_cuu_dat_phong_' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a); 
            a.click(); 
            a.remove();
            URL.revokeObjectURL(url);
        });
    </script>
    <?php endif; ?>

    <footer class="booking-footer" style="margin-top: 60px;">
        <div class="footer-content">
            <p style="font-size: 1.2em; margin-bottom: 15px;">© 2024 Khách Sạn Sang Trọng - Hotel Management System</p>
            <div class="footer-links">
                <a href="#" class="footer-link"><i class="ph ph-map-pin"></i> 123 Đường ABC, Quận XYZ, TP.HCM</a>
                <a href="#" class="footer-link"><i class="ph ph-phone"></i> Hotline: 1900-xxxx</a>
                <a href="#" class="footer-link"><i class="ph ph-envelope"></i> Email: contact@hotel.com</a>
            </div>
        </div>
    </footer>
</body>
</html>

