<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';

$auth = new CustomerAuthController();

// Kiểm tra đăng nhập
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$customerInfo = $auth->getCustomerInfo();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân</title>
    <link rel="stylesheet" href="../assets/css/booking.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .profile-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
        }
        
        .profile-header {
            max-width: 1200px;
            margin: 0 auto 30px;
            text-align: center;
            color: white;
        }
        
        .profile-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .profile-content {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .profile-banner {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 40px;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            margin: 0 auto 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            object-fit: cover;
        }
        
        .profile-name {
            color: white;
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .profile-provider {
            color: rgba(255,255,255,0.9);
            font-size: 1.1em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .profile-info {
            padding: 40px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h2 {
            color: var(--primary);
            font-size: 1.5em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-item {
            display: flex;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            min-width: 150px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            color: var(--dark);
            flex: 1;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: var(--dark);
            border: 2px solid #e9ecef;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="ph ph-user-circle"></i> Thông Tin Cá Nhân</h1>
            <p>Quản lý thông tin tài khoản của bạn</p>
        </div>
        
        <div class="profile-content">
            <div class="profile-banner">
                <img src="<?= htmlspecialchars($customerInfo['avatar']) ?>" 
                     alt="<?= htmlspecialchars($customerInfo['name']) ?>" 
                     class="profile-avatar">
                <div class="profile-name"><?= htmlspecialchars($customerInfo['name']) ?></div>
                <div class="profile-provider">
                    <?php if ($customerInfo['provider'] === 'google'): ?>
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="white" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="white" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="white" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="white" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Đăng nhập qua Google</span>
                    <?php elseif ($customerInfo['provider'] === 'facebook'): ?>
                        <i class="ph ph-facebook-logo"></i>
                        <span>Đăng nhập qua Facebook</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-info">
                <div class="info-section">
                    <h2><i class="ph ph-identification-card"></i> Thông Tin Tài Khoản</h2>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="ph ph-user"></i>
                            <span>Họ và tên:</span>
                        </div>
                        <div class="info-value"><?= htmlspecialchars($customerInfo['name']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="ph ph-envelope"></i>
                            <span>Email:</span>
                        </div>
                        <div class="info-value"><?= htmlspecialchars($customerInfo['email']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="ph ph-shield-check"></i>
                            <span>Phương thức đăng nhập:</span>
                        </div>
                        <div class="info-value">
                            <?php if ($customerInfo['provider'] === 'google'): ?>
                                <span style="color: #4285F4;">Google OAuth 2.0</span>
                            <?php elseif ($customerInfo['provider'] === 'facebook'): ?>
                                <span style="color: #1877f2;">Facebook Login</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="ph ph-fingerprint"></i>
                            <span>ID Tài khoản:</span>
                        </div>
                        <div class="info-value">#<?= $customerInfo['id'] ?></div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="ph ph-arrow-left"></i>
                        <span>Quay lại</span>
                    </a>
                    <a href="bookings.php" class="btn btn-primary">
                        <i class="ph ph-ticket"></i>
                        <span>Xem lịch sử đặt phòng</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
