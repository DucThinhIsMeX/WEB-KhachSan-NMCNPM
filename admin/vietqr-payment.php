<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../config/database.php';

$auth = new AuthController();
$auth->requireAdmin();

$database = new Database();
$db = $database->connect();

$maHoaDon = $_GET['hoadon'] ?? null;
if (!$maHoaDon) {
    header('Location: hoa-don.php');
    exit;
}

// Lấy thông tin hóa đơn
$stmt = $db->prepare("
    SELECT H.*, P.SoPhong, PT.MaPhieuThue
    FROM HOADON H
    JOIN PHIEUTHUE PT ON H.MaPhieuThue = PT.MaPhieuThue
    JOIN PHONG P ON PT.MaPhong = P.MaPhong
    WHERE H.MaHoaDon = ?
");
$stmt->execute([$maHoaDon]);
$hoaDon = $stmt->fetch();

if (!$hoaDon) {
    header('Location: hoa-don.php');
    exit;
}

// Thông tin ngân hàng (thay bằng thông tin thực tế)
$bankId = 'VCB'; // Vietcombank
$accountNo = '1234567890'; // Số tài khoản
$accountName = 'KHACH SAN SANG TRONG'; // Tên tài khoản
$amount = (int)$hoaDon['TriGia'];
$description = "HD" . $maHoaDon . " P" . $hoaDon['SoPhong'];

// Generate VietQR URL
$qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?" . http_build_query([
    'amount' => $amount,
    'addInfo' => $description,
    'accountName' => $accountName
]);

$page_title = 'Thanh Toán VietQR';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán VietQR - Hóa Đơn #<?= $maHoaDon ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .payment-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-header h1 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        
        .payment-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .payment-body {
            padding: 40px;
        }
        
        .invoice-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .invoice-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .invoice-info-row:last-child {
            border-bottom: none;
            font-size: 1.3em;
            font-weight: bold;
            color: #667eea;
            padding-top: 15px;
        }
        
        .qr-section {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .qr-section h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .qr-code-wrapper {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .qr-code-wrapper img {
            max-width: 350px;
            width: 100%;
            height: auto;
            display: block;
        }
        
        .payment-instructions {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .payment-instructions h4 {
            margin-top: 0;
            color: #856404;
        }
        
        .payment-instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .payment-instructions li {
            margin: 8px 0;
            color: #856404;
        }
        
        .bank-info {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .bank-info h4 {
            margin-top: 0;
            color: #155724;
        }
        
        .bank-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #155724;
        }
        
        .bank-info-item strong {
            font-weight: 600;
        }
        
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: 0.3s;
        }
        
        .copy-btn:hover {
            background: #5568d3;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        @media print {
            .action-buttons, .payment-header {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <?php include 'includes/header.php'; ?>
        
        <main class="main-container">
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="ph ph-check-circle"></i> Hóa đơn đã được tạo thành công! Vui lòng thanh toán bằng VietQR.
            </div>
            <?php endif; ?>
            
            <div class="payment-container">
                <div class="payment-header">
                    <h1><i class="ph ph-qr-code"></i> Thanh Toán VietQR</h1>
                    <p>Hóa đơn #<?= $maHoaDon ?> - Phòng <?= htmlspecialchars($hoaDon['SoPhong']) ?></p>
                </div>
                
                <div class="payment-body">
                    <div class="invoice-info">
                        <h3><i class="ph ph-receipt"></i> Thông Tin Hóa Đơn</h3>
                        <div class="invoice-info-row">
                            <span>Mã hóa đơn:</span>
                            <strong>#<?= $maHoaDon ?></strong>
                        </div>
                        <div class="invoice-info-row">
                            <span>Phòng:</span>
                            <strong><?= htmlspecialchars($hoaDon['SoPhong']) ?></strong>
                        </div>
                        <div class="invoice-info-row">
                            <span>Khách hàng:</span>
                            <strong><?= htmlspecialchars($hoaDon['TenKhachHangCoQuan']) ?></strong>
                        </div>
                        <div class="invoice-info-row">
                            <span>Ngày thanh toán:</span>
                            <strong><?= date('d/m/Y', strtotime($hoaDon['NgayThanhToan'])) ?></strong>
                        </div>
                        <div class="invoice-info-row">
                            <span>TỔNG TIỀN:</span>
                            <strong><?= number_format($amount) ?> VNĐ</strong>
                        </div>
                    </div>
                    
                    <div class="qr-section">
                        <h3><i class="ph ph-camera"></i> Quét Mã QR Để Thanh Toán</h3>
                        <div class="qr-code-wrapper">
                            <img src="<?= htmlspecialchars($qrUrl) ?>" alt="VietQR Code" id="qrImage">
                        </div>
                        <p style="margin-top: 15px; color: #666;">
                            Sử dụng app ngân hàng để quét mã QR
                        </p>
                    </div>
                    
                    <div class="payment-instructions">
                        <h4><i class="ph ph-info"></i> Hướng Dẫn Thanh Toán</h4>
                        <ol>
                            <li>Mở ứng dụng Mobile Banking của bạn</li>
                            <li>Chọn chức năng "Quét mã QR"</li>
                            <li>Quét mã QR code phía trên</li>
                            <li>Kiểm tra thông tin và xác nhận thanh toán</li>
                            <li>Lưu lại biên lai giao dịch</li>
                        </ol>
                    </div>
                    
                    <div class="bank-info">
                        <h4><i class="ph ph-bank"></i> Thông Tin Chuyển Khoản (Nếu Không Dùng QR)</h4>
                        <div class="bank-info-item">
                            <span>Ngân hàng:</span>
                            <strong>Vietcombank (VCB)</strong>
                        </div>
                        <div class="bank-info-item">
                            <span>Số tài khoản:</span>
                            <div>
                                <strong id="accountNumber"><?= $accountNo ?></strong>
                                <button class="copy-btn" onclick="copyText('accountNumber')">
                                    <i class="ph ph-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="bank-info-item">
                            <span>Chủ tài khoản:</span>
                            <strong><?= $accountName ?></strong>
                        </div>
                        <div class="bank-info-item">
                            <span>Số tiền:</span>
                            <div>
                                <strong id="amount"><?= number_format($amount) ?></strong>
                                <button class="copy-btn" onclick="copyText('amount', true)">
                                    <i class="ph ph-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="bank-info-item">
                            <span>Nội dung:</span>
                            <div>
                                <strong id="description"><?= $description ?></strong>
                                <button class="copy-btn" onclick="copyText('description')">
                                    <i class="ph ph-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="ph ph-printer"></i> In Hóa Đơn
                        </button>
                        <a href="hoa-don.php" class="btn btn-primary">
                            <i class="ph ph-arrow-left"></i> Quay Lại
                        </a>
                        <button onclick="downloadQR()" class="btn btn-success">
                            <i class="ph ph-download-simple"></i> Tải QR Code
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function copyText(elementId, isNumber = false) {
            const element = document.getElementById(elementId);
            let text = element.innerText;
            
            // Remove formatting for numbers
            if (isNumber) {
                text = text.replace(/\./g, '');
            }
            
            navigator.clipboard.writeText(text).then(() => {
                const btn = element.nextElementSibling;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="ph ph-check"></i> Đã copy';
                btn.style.background = '#28a745';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 2000);
            });
        }
        
        function downloadQR() {
            const qrImage = document.getElementById('qrImage');
            const link = document.createElement('a');
            link.href = qrImage.src;
            link.download = 'VietQR_HD<?= $maHoaDon ?>.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
