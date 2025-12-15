<?php
session_start();
require_once __DIR__ . '/../config/oauth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Facebook OAuth</title>
    <style>
        body { 
            font-family: Arial; 
            padding: 40px; 
            background: linear-gradient(135deg, #1877f2 0%, #0d65d9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            max-width: 600px; 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #1877f2; margin-bottom: 20px; }
        .info { background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #1877f2; }
        .btn { 
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #1877f2 0%, #0d65d9 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin: 10px 0;
            transition: 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(24, 119, 242, 0.4); }
        code { background: #f0f0f0; padding: 3px 8px; border-radius: 4px; font-size: 0.9em; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        ol { line-height: 2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔵 Test Facebook OAuth</h1>
        
        <div class="info">
            <h3>Cấu hình Facebook App:</h3>
            <p><strong>App ID:</strong> <code><?= FACEBOOK_APP_ID ?></code></p>
            <p><strong>Redirect URI:</strong> <code><?= FACEBOOK_REDIRECT_URI ?></code></p>
        </div>

        <div class="info">
            <h3>⚠️ Checklist trước khi test:</h3>
            <ol>
                <li class="<?= file_exists(__DIR__ . '/../database/hotel.db') ? 'success' : 'error' ?>">
                    <?= file_exists(__DIR__ . '/../database/hotel.db') ? '✅' : '❌' ?> Database đã khởi tạo
                </li>
                <li>✅ Chạy migration: <code>php database/migrate-facebook-fix.php</code></li>
                <li>✅ Facebook App Settings → App Domains: <code>localhost</code></li>
                <li>✅ Facebook Login → Settings → Valid OAuth Redirect URIs: <code><?= FACEBOOK_REDIRECT_URI ?></code></li>
                <li>✅ App Mode: Development (thêm test user hoặc developer)</li>
            </ol>
        </div>

        <div class="info">
            <h3>🐛 Lỗi thường gặp:</h3>
            <ul>
                <li><strong>"Can't Load URL":</strong> Dùng localhost thay vì 127.0.0.1</li>
                <li><strong>"App Not Setup":</strong> Chưa cấu hình Facebook Login</li>
                <li><strong>"App in Development Mode":</strong> Thêm tài khoản vào Roles</li>
                <li><strong>"Email not provided":</strong> OK, hệ thống sẽ tạo email dummy</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?= htmlspecialchars(getFacebookAuthUrl()) ?>" class="btn">
                🚀 Test Facebook Login
            </a>
            <br>
            <a href="login.php" class="btn" style="background: #667eea;">
                🔑 Về trang Login
            </a>
        </div>

        <?php if (isset($_SESSION['customer_id'])): ?>
        <div class="info" style="background: #d4edda; border-color: #28a745;">
            <h3 class="success">✅ Đã đăng nhập!</h3>
            <p><strong>Tên:</strong> <?= htmlspecialchars($_SESSION['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['customer_email']) ?></p>
            <p><strong>Provider:</strong> <?= htmlspecialchars($_SESSION['customer_provider']) ?></p>
            <a href="logout.php" style="color: #dc3545; text-decoration: none;">🚪 Đăng xuất</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
