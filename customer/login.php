<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';
require_once __DIR__ . '/../config/oauth.php';

$auth = new CustomerAuthController();

// Nếu đã đăng nhập, chuyển về trang chủ
if ($auth->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Tạo Google OAuth URL
$googleAuthUrl = GOOGLE_AUTH_URL;
$googleAuthUrl .= '?client_id=' . GOOGLE_CLIENT_ID;
$googleAuthUrl .= '&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URI);
$googleAuthUrl .= '&response_type=code';
$googleAuthUrl .= '&scope=' . urlencode('email profile');
$googleAuthUrl .= '&access_type=offline';
$googleAuthUrl .= '&prompt=consent';

// Tạo Facebook OAuth URL
$facebookAuthUrl = FACEBOOK_AUTH_URL;
$facebookAuthUrl .= '?client_id=' . FACEBOOK_APP_ID;
$facebookAuthUrl .= '&redirect_uri=' . urlencode(FACEBOOK_REDIRECT_URI);
$facebookAuthUrl .= '&scope=' . urlencode('email public_profile');
$facebookAuthUrl .= '&response_type=code';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Khách Hàng</title>
    <link rel="stylesheet" href="../assets/css/booking.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            animation: fadeInUp 0.6s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header i {
            font-size: 4em;
            color: var(--primary);
            margin-bottom: 20px;
            display: block;
        }
        
        .login-header h1 {
            color: var(--dark);
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 1.1em;
        }
        
        .oauth-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .oauth-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05em;
            text-decoration: none;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .oauth-btn i {
            font-size: 1.5em;
        }
        
        .google-btn {
            background: white;
            color: #333;
            border: 2px solid #ddd;
        }
        
        .google-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .facebook-btn {
            background: #1877f2;
            color: white;
        }
        
        .facebook-btn:hover {
            background: #166fe5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(24,119,242,0.4);
        }
        
        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            color: #666;
            font-weight: 500;
        }
        
        .admin-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .admin-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-link a:hover {
            text-decoration: underline;
        }
        
        .back-home {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-home a {
            color: #666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-home a:hover {
            color: var(--primary);
        }
        
        .features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
        }
        
        .features h3 {
            color: var(--dark);
            font-size: 1.1em;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }
        
        .feature-item i {
            color: var(--success);
        }
        
        .google-icon {
            width: 24px;
            height: 24px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background: #fee;
            border: 2px solid #f00;
            color: #c00;
        }
        
        .alert-success {
            background: #dfd;
            border: 2px solid #0a0;
            color: #080;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="ph ph-user-circle"></i>
                <h1>Đăng Nhập Khách Hàng</h1>
                <p>Đăng nhập để đặt phòng và quản lý booking của bạn</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="ph ph-warning-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="ph ph-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>
            
            <div class="oauth-buttons">
                <a href="<?= htmlspecialchars($googleAuthUrl) ?>" class="oauth-btn google-btn">
                    <svg class="google-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Đăng nhập với Google</span>
                </a>
                
                <a href="<?= htmlspecialchars($facebookAuthUrl) ?>" class="oauth-btn facebook-btn">
                    <i class="ph ph-facebook-logo"></i>
                    <span>Đăng nhập với Facebook</span>
                </a>
            </div>
            
            <div class="features">
                <h3><i class="ph ph-sparkle"></i> Lợi ích khi đăng nhập:</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="ph ph-check-circle"></i>
                        <span>Đặt phòng nhanh chóng</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-check-circle"></i>
                        <span>Quản lý lịch sử booking</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-check-circle"></i>
                        <span>Nhận ưu đãi độc quyền</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-check-circle"></i>
                        <span>Lưu thông tin thanh toán</span>
                    </div>
                </div>
            </div>
            
            <div class="divider">
                <span>hoặc</span>
            </div>
            
            <div class="admin-link">
                <a href="../admin/login.php">
                    <i class="ph ph-shield-check"></i>
                    <span>Đăng nhập dành cho Quản trị viên</span>
                </a>
            </div>
            
            <div class="back-home">
                <a href="../index.php">
                    <i class="ph ph-arrow-left"></i>
                    <span>Quay lại trang chủ</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
