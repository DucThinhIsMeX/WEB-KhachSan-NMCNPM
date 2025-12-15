<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController();

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'access_denied') {
    $error = 'Bạn không có quyền truy cập. Vui lòng đăng nhập!';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị - Hotel Management</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            border-radius: 50%;
        }
        
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(118, 75, 162, 0.15) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
            border-radius: 50%;
        }
        
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1000px;
            display: flex;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .login-banner {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-banner::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }
        
        .login-banner::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }
        
        .banner-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .banner-icon {
            font-size: 120px;
            margin-bottom: 30px;
            opacity: 0.95;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .banner-content h2 {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .banner-content p {
            font-size: 1.1em;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .banner-features {
            margin-top: 40px;
            text-align: left;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(5px);
        }
        
        .feature-item i {
            font-size: 24px;
        }
        
        .feature-item span {
            font-size: 0.95em;
        }
        
        .login-form-container {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            margin-bottom: 40px;
        }
        
        .login-header h1 {
            font-size: 2em;
            color: #1a202c;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .login-header h1 i {
            color: #667eea;
        }
        
        .login-header p {
            color: #718096;
            font-size: 1em;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            background: #fee;
            border-left: 4px solid #ef4444;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert i {
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 28px;
            position: relative;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            color: #374151;
            font-weight: 600;
            font-size: 0.95em;
        }
        
        .form-group label i {
            font-size: 18px;
            color: #667eea;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f9fafb;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }
        
        .form-group input::placeholder {
            color: #9ca3af;
        }
        
        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            font-size: 20px;
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #9ca3af;
            font-size: 0.9em;
            position: relative;
        }
        
        .customer-login-link {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .customer-login-link:hover {
            background: #f3f4f6;
        }
        
        .customer-login-link p {
            color: #6b7280;
            font-size: 0.95em;
            margin-bottom: 12px;
        }
        
        .customer-login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 1em;
        }
        
        .customer-login-link a:hover {
            color: #764ba2;
            gap: 12px;
        }
        
        .customer-login-link a i {
            font-size: 20px;
        }
        
        .login-footer {
            margin-top: 30px;
            text-align: center;
            color: #9ca3af;
            font-size: 0.9em;
        }
        
        .login-footer p {
            margin-bottom: 8px;
        }
        
        .default-credentials {
            margin-top: 15px;
            padding: 12px 16px;
            background: #fef3c7;
            border-radius: 8px;
            border: 1px solid #fbbf24;
            font-size: 0.85em;
            color: #92400e;
        }
        
        .default-credentials strong {
            color: #78350f;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .login-banner {
                display: none;
            }
            
            .login-form-container {
                padding: 40px 30px;
            }
            
            .login-wrapper {
                max-width: 450px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Banner -->
        <div class="login-banner">
            <div class="banner-content">
                <i class="ph-fill ph-buildings banner-icon"></i>
                <h2>Hệ Thống Quản Lý<br>Khách Sạn</h2>
                <p>Giải pháp quản lý toàn diện và chuyên nghiệp</p>
                
                <div class="banner-features">
                    <div class="feature-item">
                        <i class="ph-fill ph-calendar-check"></i>
                        <span>Quản lý đặt phòng hiệu quả</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph-fill ph-users-three"></i>
                        <span>Theo dõi khách hàng dễ dàng</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph-fill ph-chart-line"></i>
                        <span>Báo cáo & thống kê chi tiết</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Form -->
        <div class="login-form-container">
            <div class="login-header">
                <h1>
                    <i class="ph-fill ph-shield-check"></i>
                    Đăng Nhập Quản Trị
                </h1>
                <p>Vui lòng nhập thông tin đăng nhập của bạn</p>
            </div>

            <?php if ($error): ?>
                <div class="alert">
                    <i class="ph-fill ph-warning-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label>
                        <i class="ph-fill ph-user"></i>
                        Tên đăng nhập
                    </label>
                    <div class="input-wrapper">
                        <input type="text" name="username" required 
                               placeholder="Nhập tên đăng nhập của bạn"
                               autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="ph-fill ph-lock-key"></i>
                        Mật khẩu
                    </label>
                    <div class="input-wrapper">
                        <input type="password" name="password" required 
                               placeholder="Nhập mật khẩu của bạn"
                               autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="ph-fill ph-sign-in"></i>
                    Đăng Nhập
                </button>
            </form>

            <div class="divider">
                <span>hoặc</span>
            </div>

            <div class="customer-login-link">
                <p>Bạn là khách hàng?</p>
                <a href="../customer/login.php">
                    <i class="ph-fill ph-user-circle"></i>
                    Đăng nhập khách hàng
                </a>
            </div>

            <div class="login-footer">
                <p>&copy; 2024 Hotel Management System. All rights reserved.</p>
                <div class="default-credentials">
                    <i class="ph-fill ph-info"></i> Tài khoản mặc định: 
                    <strong>admin</strong> / <strong>admin123</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
