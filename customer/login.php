<?php
session_start();
require_once __DIR__ . '/../config/oauth.php';

if (isset($_SESSION['customer_id'])) {
    header('Location: ../index.php');
    exit;
}

$googleUrl = getGoogleAuthUrl();
$facebookUrl = getFacebookAuthUrl();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Khách Sạn</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0e27;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Background Animation */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .bg-animation::before {
            content: '';
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
            top: -400px;
            right: -400px;
            animation: float 20s ease-in-out infinite;
        }
        
        .bg-animation::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 70%);
            bottom: -300px;
            left: -300px;
            animation: float 15s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(50px, -50px) rotate(120deg); }
            66% { transform: translate(-50px, 50px) rotate(240deg); }
        }
        
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 95%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 30px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            backdrop-filter: blur(20px);
        }
        
        /* Left Side - Hero Image */
        .login-hero {
            position: relative;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                        url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80') center/cover;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            overflow: hidden;
        }
        
        .login-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 50px,
                rgba(255, 255, 255, 0.03) 50px,
                rgba(255, 255, 255, 0.03) 100px
            );
            animation: slidePattern 30s linear infinite;
        }
        
        @keyframes slidePattern {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            font-size: 28px;
            font-weight: 800;
            font-family: 'Playfair Display', serif;
        }
        
        .hero-logo i {
            font-size: 40px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .hero-title {
            font-size: 3em;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .hero-subtitle {
            font-size: 1.2em;
            opacity: 0.95;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        
        .hero-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: 0.3s;
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(10px);
        }
        
        .feature-item i {
            font-size: 28px;
        }
        
        .feature-text h4 {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .feature-text p {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .hero-stats {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: 800;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        /* Right Side - Login Form */
        .login-form-section {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            margin-bottom: 40px;
        }
        
        .login-header h1 {
            font-size: 2.5em;
            color: #1a202c;
            font-weight: 800;
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        
        .login-header p {
            color: #718096;
            font-size: 1.1em;
        }
        
        .error { 
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fee2e2; 
            color: #dc2626; 
            padding: 16px 20px; 
            border-radius: 12px; 
            margin-bottom: 30px;
            border-left: 4px solid #ef4444;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .error i {
            font-size: 22px;
        }
        
        .social-login {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .btn { 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 18px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn i {
            font-size: 24px;
        }
        
        .btn-google { 
            background: white; 
            color: #333; 
            border: 2px solid #e5e7eb; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .btn-google:hover { 
            border-color: #4285f4; 
            box-shadow: 0 8px 20px rgba(66, 133, 244, 0.25);
            transform: translateY(-2px);
        }
        
        .btn-facebook { 
            background: #1877f2; 
            color: white; 
            border: 2px solid #1877f2;
            box-shadow: 0 2px 8px rgba(24, 119, 242, 0.3);
        }
        
        .btn-facebook:hover { 
            background: #0d65d9; 
            box-shadow: 0 8px 20px rgba(24, 119, 242, 0.4);
            transform: translateY(-2px);
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 35px 0;
            color: #9ca3af;
            font-size: 0.9em;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .divider span {
            padding: 0 15px;
            background: white;
        }
        
        .admin-section {
            padding: 25px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 14px;
            text-align: center;
        }
        
        .admin-section p {
            color: #cbd5e1;
            font-size: 0.95em;
            margin-bottom: 15px;
        }
        
        .btn-admin {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .btn-admin:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }
        
        .back-link { 
            text-align: center; 
            margin-top: 30px;
        }
        
        .back-link a { 
            color: #667eea; 
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        
        .back-link a:hover {
            color: #764ba2;
            gap: 12px;
        }
        
        .trust-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        
        .trust-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 0.9em;
        }
        
        .trust-badge i {
            color: #10b981;
            font-size: 20px;
        }
        
        @media (max-width: 968px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .login-hero {
                display: none;
            }
            
            .login-form-section {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="login-wrapper">
        <!-- Left Hero Section -->
        <div class="login-hero">
            <div class="hero-content">
                <div class="hero-logo">
                    <i class="ph-fill ph-buildings"></i>
                    <span>Premium Hotels</span>
                </div>
                
                <h2 class="hero-title">Trải Nghiệm<br>Sang Trọng<br>Đẳng Cấp</h2>
                
                <p class="hero-subtitle">
                    Đặt phòng dễ dàng, nhanh chóng với dịch vụ chuyên nghiệp và chất lượng hàng đầu
                </p>
                
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="ph-fill ph-check-circle"></i>
                        <div class="feature-text">
                            <h4>Đặt Phòng Nhanh</h4>
                            <p>Chỉ trong vài phút</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="ph-fill ph-star"></i>
                        <div class="feature-text">
                            <h4>Dịch Vụ 5 Sao</h4>
                            <p>Chất lượng hàng đầu</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="ph-fill ph-shield-check"></i>
                        <div class="feature-text">
                            <h4>Bảo Mật Tuyệt Đối</h4>
                            <p>Thông tin được bảo vệ</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">5000+</span>
                    <span class="stat-label">Khách hàng</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4.9/5</span>
                    <span class="stat-label">Đánh giá</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Hỗ trợ</span>
                </div>
            </div>
        </div>
        
        <!-- Right Form Section -->
        <div class="login-form-section">
            <div class="login-header">
                <h1>Chào Mừng!</h1>
                <p>Đăng nhập để bắt đầu trải nghiệm</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error">
                    <i class="ph-fill ph-warning-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="social-login">
                <a href="<?= htmlspecialchars($googleUrl) ?>" class="btn btn-google">
                    <i class="ph-logo ph-google-logo"></i>
                    Đăng nhập bằng Google
                </a>
                
                <a href="<?= htmlspecialchars($facebookUrl) ?>" class="btn btn-facebook">
                    <i class="ph-logo ph-facebook-logo"></i>
                    Đăng nhập bằng Facebook
                </a>
            </div>
            
            <div class="trust-badges">
                <div class="trust-badge">
                    <i class="ph-fill ph-shield-check"></i>
                    <span>Bảo mật</span>
                </div>
                <div class="trust-badge">
                    <i class="ph-fill ph-lock-key"></i>
                    <span>Mã hóa</span>
                </div>
                <div class="trust-badge">
                    <i class="ph-fill ph-check-circle"></i>
                    <span>Xác thực</span>
                </div>
            </div>
            
            <div class="divider">
                <span>Quản trị viên</span>
            </div>
            
            <div class="admin-section">
                <p>Bạn là quản trị viên?</p>
                <a href="../admin/login.php" class="btn btn-admin">
                    <i class="ph-fill ph-shield-star"></i>
                    Đăng nhập quản trị
                </a>
            </div>
            
            <div class="back-link">
                <a href="../index.php">
                    <i class="ph-bold ph-arrow-left"></i>
                    Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
