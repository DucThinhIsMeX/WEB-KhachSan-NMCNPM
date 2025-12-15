<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../controllers/KhachHangController.php';

$database = new Database();
$db = $database->connect();
$phieuThueCtrl = new PhieuThueController();
$khachHangCtrl = new KhachHangController();

$message = '';
$error = '';
$maPhieuThue = null;

// Xử lý đặt phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        // Tạo phiếu thuê
        $maPhieuThue = $phieuThueCtrl->taoPhieuThue(
            $_POST['maPhong'],
            $_POST['ngayBatDau'],
            $danhSachKhach
        );
        
        $message = "Đặt phòng thành công! Mã phiếu thuê của bạn là: #$maPhieuThue";
    } catch (Exception $e) {
        $error = "Lỗi đặt phòng: " . $e->getMessage();
    }
}

// Lấy thông tin phòng
if (!isset($_GET['phong'])) {
    header('Location: ../index.php');
    exit;
}

$maPhong = $_GET['phong'];
$stmt = $db->prepare("SELECT P.*, L.TenLoai, L.DonGiaCoBan FROM PHONG P 
                      JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                      WHERE P.MaPhong = ?");
$stmt->execute([$maPhong]);
$phong = $stmt->fetch();

if (!$phong || $phong['TinhTrang'] !== 'Trống') {
    header('Location: ../index.php');
    exit;
}

$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');
$tlPhuThu = $database->getThamSo('TL_PHU_THU_KHACH_3');
$hsKhachNN = $database->getThamSo('HS_KHACH_NUOC_NGOAI');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Phòng <?= $phong['SoPhong'] ?> - Khách Sạn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            color: var(--gray-800);
        }
        
        .booking-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .booking-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 24px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .header-content {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header-content h1 {
            font-size: 1.875em;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header-content h1 i {
            font-size: 0.9em;
            vertical-align: middle;
            margin-right: 8px;
        }
        
        .header-content p {
            text-align: center;
            font-size: 0.95em;
            opacity: 1;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .booking-nav {
            margin-top: 16px;
            text-align: center;
        }
        
        .booking-nav a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: white;
            text-decoration: none;
            margin: 0 8px;
            font-weight: 500;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.9em;
        }
        
        .booking-nav a:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        
        .booking-main {
            flex: 1;
            padding: 32px 20px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .success-message {
            background: white;
            border: 2px solid var(--success);
            color: var(--gray-800);
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
            width: 100%;
        }
        
        .success-message h2 {
            color: var(--success);
            margin-bottom: 16px;
            font-size: 1.75em;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .success-message h2 i {
            font-size: 1.1em;
        }
        
        .success-message .booking-code {
            font-size: 2em;
            font-weight: 800;
            color: var(--primary);
            margin: 20px 0;
            padding: 16px 24px;
            background: var(--gray-50);
            border-radius: 12px;
            letter-spacing: 1px;
        }
        
        .success-info {
            background: var(--gray-50);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: left;
        }
        
        .success-info p {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
            font-size: 0.95em;
            color: var(--gray-700);
        }
        
        .success-info i {
            font-size: 20px;
            color: var(--primary);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95em;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        
        .btn i {
            font-size: 18px;
        }
        
        .room-summary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            width: 100%;
        }
        
        .room-summary h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }
        
        .room-summary .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .room-summary .info-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 16px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .room-summary .info-item span {
            display: block;
            font-size: 0.85em;
            opacity: 0.9;
            margin-bottom: 6px;
        }
        
        .room-summary .info-item strong {
            display: block;
            font-size: 1.25em;
            font-weight: 700;
        }
        
        .estimate-box {
            background: rgba(255, 255, 255, 0.15);
            padding: 16px 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .estimate-box > div:first-child {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
            font-weight: 600;
        }
        
        .estimate-value {
            font-size: 1.5em;
            font-weight: 800;
            color: #fbbf24;
        }
        
        .booking-form {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            width: 100%;
        }
        
        .booking-form h2 {
            color: var(--gray-800);
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.9em;
        }
        
        .form-group label i {
            font-size: 16px;
            color: var(--primary);
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            font-size: 0.95em;
            transition: all 0.2s;
            background: white;
            font-family: 'Inter', sans-serif;
            color: var(--gray-800);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-group small {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--gray-600);
            font-size: 0.8em;
            margin-top: 6px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .required-label::after {
            content: " *";
            color: var(--danger);
            font-weight: bold;
        }
        
        .khach-section {
            border: 1.5px solid var(--gray-200);
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            background: var(--gray-50);
        }
        
        .khach-section h3 {
            color: var(--primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1em;
            font-weight: 700;
        }
        
        .khach-section h3 i {
            font-size: 24px;
        }
        
        .toggle-khach {
            background: var(--primary);
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.2s;
            margin: 0 6px;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }
        
        .toggle-khach:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 0.9em;
        }
        
        .alert-error {
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fecaca;
        }
        
        .alert i {
            font-size: 20px;
        }
        
        .hidden {
            display: none;
        }
        
        .booking-footer {
            background: var(--gray-800);
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: auto;
        }
        
        .booking-footer p {
            opacity: 0.8;
            font-size: 0.9em;
        }
        
        hr {
            border: none;
            height: 1px;
            background: var(--gray-200);
            margin: 24px 0;
        }
        
        @media (max-width: 768px) {
            .booking-header {
                padding: 20px;
            }
            
            .header-content h1 {
                font-size: 1.5em;
            }
            
            .booking-main {
                padding: 20px 16px;
            }
            
            .booking-form {
                padding: 24px 20px;
            }
            
            .room-summary {
                padding: 20px;
            }
            
            .room-summary .info-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn {
                width: 100%;
                padding: 13px;
            }
            
            .success-message {
                padding: 24px 20px;
            }
            
            .success-message .booking-code {
                font-size: 1.5em;
            }
            
            .estimate-box {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .toggle-khach {
                width: 100%;
                margin: 8px 0;
            }
            
            .khach-section {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>
                    <i class="ph-fill ph-sparkle"></i> 
                    Đặt Phòng Trực Tuyến
                </h1>
                <p>Hoàn tất thông tin để đặt phòng của bạn</p>
            </div>
            <nav class="booking-nav">
                <a href="../index.php">
                    <i class="ph-fill ph-house"></i> Trang chủ
                </a>
                <a href="tra-cuu-dat-phong.php">
                    <i class="ph-fill ph-magnifying-glass"></i> Tra cứu đặt phòng
                </a>
            </nav>
        </header>

        <main class="booking-main">
            <?php if ($maPhieuThue): ?>
            <!-- Thông báo đặt phòng thành công -->
            <div class="success-message">
                <h2>
                    <i class="ph-fill ph-check-circle"></i> 
                    Đặt Phòng Thành Công!
                </h2>
                <p style="font-size: 1.1em;">Cảm ơn bạn đã đặt phòng tại khách sạn của chúng tôi.</p>
                <div class="booking-code">
                    <i class="ph-fill ph-ticket"></i> 
                    #<?= $maPhieuThue ?>
                </div>
                <p style="font-weight: 600; font-size: 1.05em;">
                    <i class="ph-fill ph-info"></i> 
                    Vui lòng lưu lại mã này để tra cứu và check-in
                </p>
                
                <div class="success-info">
                    <p>
                        <i class="ph-fill ph-calendar-check"></i> 
                        Ngày nhận phòng: <strong><?= date('d/m/Y', strtotime($_POST['ngayBatDau'])) ?></strong>
                    </p>
                    <p>
                        <i class="ph-fill ph-bed"></i> 
                        Phòng: <strong><?= $phong['SoPhong'] ?> - <?= $phong['TenLoai'] ?></strong>
                    </p>
                    <p>
                        <i class="ph-fill ph-currency-circle-dollar"></i> 
                        Đơn giá: <strong><?= number_format($phong['DonGiaCoBan']) ?>đ/đêm</strong>
                    </p>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                    <a href="../index.php" class="btn" style="background: var(--gray-600); box-shadow: 0 2px 6px rgba(75, 85, 99, 0.25);">
                        <i class="ph-fill ph-arrow-left"></i> Về trang chủ
                    </a>
                    <a href="tra-cuu-dat-phong.php?keyword=<?= $maPhieuThue ?>&search=1" class="btn">
                        <i class="ph-fill ph-magnifying-glass"></i> Xem chi tiết
                    </a>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph-fill ph-warning-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Thông tin phòng -->
            <div class="room-summary">
                <h2>
                    <i class="ph-fill ph-buildings"></i> 
                    Thông Tin Phòng Đã Chọn
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span><i class="ph-fill ph-hash"></i> Số Phòng</span>
                        <strong><?= $phong['SoPhong'] ?></strong>
                    </div>
                    <div class="info-item">
                        <span><i class="ph-fill ph-bed"></i> Loại Phòng</span>
                        <strong><?= $phong['TenLoai'] ?></strong>
                    </div>
                    <div class="info-item">
                        <span><i class="ph-fill ph-currency-circle-dollar"></i> Đơn Giá</span>
                        <strong><?= number_format($phong['DonGiaCoBan']) ?>đ/đêm</strong>
                    </div>
                    <div class="info-item">
                        <span><i class="ph-fill ph-users"></i> Sức Chứa</span>
                        <strong>Tối đa <?= $soKhachToiDa ?> khách</strong>
                    </div>
                </div>
                <div class="estimate-box" role="status" aria-live="polite">
                    <div>
                        <i class="ph-fill ph-diamond"></i> 
                        Giá ước tính:
                    </div>
                    <div class="estimate-value" id="estimateValue"><?= number_format($phong['DonGiaCoBan']) ?>đ</div>
                </div>
                <?php if ($phong['GhiChu']): ?>
                <p style="margin-top: 20px; font-style: italic; opacity: 0.95;">
                    <i class="ph-fill ph-note-pencil"></i> <?= $phong['GhiChu'] ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Form đặt phòng -->
            <div class="booking-form">
                <h2>
                    <i class="ph-fill ph-notepad"></i> 
                    Thông Tin Đặt Phòng
                </h2>
                
                <form method="POST" id="formDatPhong">
                    <input type="hidden" name="maPhong" value="<?= $phong['MaPhong'] ?>">
                    
                    <div class="form-group">
                        <label class="required-label">
                            <i class="ph-fill ph-calendar-check"></i> 
                            Ngày Nhận Phòng
                        </label>
                        <input type="date" name="ngayBatDau" 
                               value="<?= date('Y-m-d') ?>" 
                               min="<?= date('Y-m-d') ?>"
                               required>
                        <small>
                            <i class="ph-fill ph-clock"></i> 
                            Giờ nhận phòng: 14:00. Giờ trả phòng: 12:00
                        </small>
                    </div>

                    <hr>

                    <h2>
                        <i class="ph-fill ph-users-three"></i> 
                        Thông Tin Khách Hàng
                    </h2>
                    <p style="color: var(--gray-700); margin-bottom: 20px; padding: 12px 16px; background: #fef3c7; border-radius: 10px; border-left: 3px solid var(--warning); font-size: 0.9em;">
                        <strong><i class="ph-fill ph-info"></i> Lưu ý:</strong> Tối đa <?= $soKhachToiDa ?> khách/phòng. 
                        Khách thứ <?= $soKhachToiDa ?> sẽ phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%.
                    </p>

                    <!-- Khách 1 (bắt buộc) -->
                    <div class="khach-section">
                        <h3>
                            <i class="ph-fill ph-user-circle"></i>
                            <span>Khách Hàng 1 (Người đặt phòng)</span>
                            <span style="color: #ef4444; font-size: 0.85em; margin-left: auto;">* Bắt buộc</span>
                        </h3>
                        
                        <div class="form-group">
                            <label class="required-label">
                                <i class="ph-fill ph-identification-card"></i>
                                Họ và Tên
                            </label>
                            <input type="text" name="tenKhach1" required 
                                   placeholder="Nhập họ và tên đầy đủ">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="required-label">
                                    <i class="ph-fill ph-flag"></i>
                                    Loại Khách
                                </label>
                                <select name="loaiKhach1" required>
                                    <option value="Nội địa">🇻🇳 Nội địa</option>
                                    <option value="Nước ngoài">🌍 Nước ngoài (Hệ số ×<?= $database->getThamSo('HS_KHACH_NUOC_NGOAI') ?>)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="required-label">
                                    <i class="ph-fill ph-identification-badge"></i>
                                    CMND/CCCD/Passport
                                </label>
                                <input type="text" name="cmnd1" required 
                                       placeholder="Số giấy tờ tùy thân">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required-label">
                                <i class="ph-fill ph-map-pin"></i> 
                                Địa Chỉ
                            </label>
                            <input type="text" name="diaChi1" required 
                                   placeholder="Số nhà, Đường, Quận/Huyện, Tỉnh/TP">
                        </div>
                    </div>

                    <!-- Khách 2 (tùy chọn) -->
                    <div class="khach-section hidden" id="khach2Section">
                        <h3>
                            <i class="ph-fill ph-user-circle"></i>
                            <span>Khách Hàng 2</span>
                            <span style="color: #9ca3af; font-size: 0.85em; margin-left: auto;">Tùy chọn</span>
                        </h3>
                        
                        <div class="form-group">
                            <label>
                                <i class="ph-fill ph-identification-card"></i>
                                Họ và Tên
                            </label>
                            <input type="text" name="tenKhach2" 
                                   placeholder="Nhập họ và tên đầy đủ">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <i class="ph-fill ph-flag"></i>
                                    Loại Khách
                                </label>
                                <select name="loaiKhach2">
                                    <option value="Nội địa">🇻🇳 Nội địa</option>
                                    <option value="Nước ngoài">🌍 Nước ngoài</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <i class="ph-fill ph-identification-badge"></i>
                                    CMND/CCCD/Passport
                                </label>
                                <input type="text" name="cmnd2" 
                                       placeholder="Số giấy tờ tùy thân">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="ph-fill ph-map-pin"></i>
                                Địa Chỉ
                            </label>
                            <input type="text" name="diaChi2" 
                                   placeholder="Số nhà, Đường, Quận/Huyện, Tỉnh/TP">
                        </div>
                    </div>

                    <!-- Khách 3 (tùy chọn - có phụ thu) -->
                    <div class="khach-section hidden" id="khach3Section">
                        <h3>
                            <i class="ph-fill ph-user-circle"></i>
                            <span>Khách Hàng 3</span>
                            <span style="color: #f59e0b; font-size: 0.85em; margin-left: auto;">
                                <i class="ph-fill ph-warning"></i> Phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%
                            </span>
                        </h3>
                        
                        <div class="form-group">
                            <label>
                                <i class="ph-fill ph-identification-card"></i>
                                Họ và Tên
                            </label>
                            <input type="text" name="tenKhach3" 
                                   placeholder="Nhập họ và tên đầy đủ">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <i class="ph-fill ph-flag"></i>
                                    Loại Khách
                                </label>
                                <select name="loaiKhach3">
                                    <option value="Nội địa">🇻🇳 Nội địa</option>
                                    <option value="Nước ngoài">🌍 Nước ngoài</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <i class="ph-fill ph-identification-badge"></i>
                                    CMND/CCCD/Passport
                                </label>
                                <input type="text" name="cmnd3" 
                                       placeholder="Số giấy tờ tùy thân">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="ph-fill ph-map-pin"></i>
                                Địa Chỉ
                            </label>
                            <input type="text" name="diaChi3" 
                                   placeholder="Số nhà, Đường, Quận/Huyện, Tỉnh/TP">
                        </div>
                    </div>

                    <!-- Nút thêm khách -->
                    <div style="text-align: center; margin: 30px 0;">
                        <button type="button" class="toggle-khach" id="btnKhach2" onclick="toggleKhach(2)">
                            <i class="ph-fill ph-plus-circle"></i> Thêm Khách Hàng 2
                        </button>
                        <button type="button" class="toggle-khach hidden" id="btnKhach3" onclick="toggleKhach(3)">
                            <i class="ph-fill ph-plus-circle"></i> Thêm Khách Hàng 3 (Phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%)
                        </button>
                    </div>

                    <hr>

                    <!-- Điều khoản -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 12px; background: var(--gray-50); border-radius: 10px; border: 1px solid var(--gray-200);">
                            <input type="checkbox" required style="margin-top: 2px; width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary);">
                            <span style="flex: 1; font-size: 0.9em; color: var(--gray-700);">
                                Tôi đã đọc và đồng ý với 
                                <a href="#" style="color: var(--primary); font-weight: 600;">điều khoản sử dụng</a> 
                                và 
                                <a href="#" style="color: var(--primary); font-weight: 600;">chính sách bảo mật</a>
                            </span>
                        </label>
                    </div>

                    <!-- Nút submit -->
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
                        <a href="../index.php" class="btn" style="background: var(--gray-600); box-shadow: 0 2px 6px rgba(75, 85, 99, 0.25);">
                            <i class="ph-fill ph-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn">
                            <i class="ph-fill ph-check-circle"></i> Xác Nhận Đặt Phòng
                        </button>
                    </div>
                </form>
            </div>

            <?php endif; ?>
        </main>

        <footer class="booking-footer">
            <p>
                <i class="ph-fill ph-buildings"></i> 
                © 2024 Khách sạn - Hệ thống đặt phòng trực tuyến
            </p>
        </footer>
    </div>

    <script>
        function toggleKhach(soKhach) {
            const section = document.getElementById('khach' + soKhach + 'Section');
            const btn = document.getElementById('btnKhach' + soKhach);
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                btn.innerHTML = '<i class="ph-fill ph-minus-circle"></i> Bỏ Khách Hàng ' + soKhach;
                btn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                
                // Hiện nút thêm khách tiếp theo
                if (soKhach === 2) {
                    document.getElementById('btnKhach3').classList.remove('hidden');
                }
            } else {
                section.classList.add('hidden');
                const phuThu = soKhach === 3 ? ' (Phụ thu <?= $database->getThamSo("TL_PHU_THU_KHACH_3") * 100 ?>%)' : '';
                btn.innerHTML = '<i class="ph-fill ph-plus-circle"></i> Thêm Khách Hàng ' + soKhach + phuThu;
                btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                
                // Clear input values
                section.querySelectorAll('input, select').forEach(input => {
                    if (input.type !== 'hidden') {
                        input.value = input.tagName === 'SELECT' ? 'Nội địa' : '';
                    }
                });
                
                // Ẩn nút thêm khách tiếp theo
                if (soKhach === 2) {
                    document.getElementById('btnKhach3').classList.add('hidden');
                    const khach3Section = document.getElementById('khach3Section');
                    if (!khach3Section.classList.contains('hidden')) {
                        toggleKhach(3);
                    }
                }
            }
            updateEstimate();
        }

        // Tính ước lượng tiền phòng dựa trên tham số hệ thống và số khách
        const donGiaCoBan = <?= json_encode(floatval($phong['DonGiaCoBan'])) ?>;
        const soKhachToiDaParam = <?= json_encode(intval($soKhachToiDa)) ?>;
        const tlPhuThu = <?= json_encode(floatval($tlPhuThu)) ?>;
        const hsKhachNN = <?= json_encode(floatval($hsKhachNN)) ?>;

        function currencyFormat(n) {
            return n.toLocaleString('vi-VN') + 'đ';
        }

        function getActiveGuestCount() {
            let count = 0;
            for (let i = 1; i <= 3; i++) {
                const section = document.getElementById('khach' + i + 'Section');
                if (i === 1 || (section && !section.classList.contains('hidden'))) {
                    const name = document.querySelector('input[name="tenKhach' + i + '"]');
                    if (name && name.value.trim() !== '') count++;
                }
            }
            return count;
        }

        function hasForeignGuest() {
            for (let i = 1; i <= 3; i++) {
                const section = document.getElementById('khach' + i + 'Section');
                if (i === 1 || (section && !section.classList.contains('hidden'))) {
                    const select = document.querySelector('select[name="loaiKhach' + i + '"]');
                    if (select && select.value === 'Nước ngoài') return true;
                }
            }
            return false;
        }

        function updateEstimate() {
            const days = 1; // Default 1 đêm (không có ngày trả)
            let price = donGiaCoBan;

            const activeGuests = getActiveGuestCount() || 1;
            if (activeGuests >= soKhachToiDaParam) {
                price *= (1 + tlPhuThu);
            }
            if (hasForeignGuest()) price *= hsKhachNN;

            const total = Math.round(price * days);
            document.getElementById('estimateValue').textContent = currencyFormat(total);
        }

        // Các sự kiện thay đổi để cập nhật ước lượng
        document.querySelectorAll('select[name^="loaiKhach"], input[name^="tenKhach"]').forEach(el => {
            el.addEventListener('change', updateEstimate);
            el.addEventListener('input', updateEstimate);
        });
        document.querySelector('input[name="ngayBatDau"]').addEventListener('change', updateEstimate);

        // Đặt ước lượng khi tải trang
        updateEstimate();

        // Validate form trước khi submit
        document.getElementById('formDatPhong').addEventListener('submit', function(e) {
            const ngayBatDau = new Date(document.querySelector('input[name="ngayBatDau"]').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (ngayBatDau < today) {
                e.preventDefault();
                alert('⚠️ Ngày nhận phòng phải từ hôm nay trở đi!');
                return false;
            }
            
            return confirm('✅ Xác nhận đặt phòng với thông tin đã nhập?\n\nVui lòng kiểm tra kỹ thông tin trước khi xác nhận.');
        });
        
        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>

