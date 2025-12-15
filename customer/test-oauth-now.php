<?php
require_once __DIR__ . '/../config/oauth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test OAuth Google</title>
    <style>
        body { 
            font-family: Arial; 
            padding: 40px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            text-align: center;
        }
        h1 { color: #667eea; margin-bottom: 20px; }
        .info-box { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 20px 0;
            text-align: left;
        }
        .info-box strong { color: #667eea; }
        .success { color: #28a745; font-weight: bold; }
        .btn { 
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin: 10px 5px;
            transition: 0.3s;
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        code { 
            background: #e9ecef; 
            padding: 3px 8px; 
            border-radius: 4px;
            font-size: 0.9em;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Test OAuth Google</h1>
        
        <div class="info-box">
            <h3>✅ Credentials đã cấu hình:</h3>
            <p><strong>Client ID:</strong><br>
            <code><?= htmlspecialchars(GOOGLE_CLIENT_ID) ?></code></p>
            
            <p><strong>Client Secret:</strong><br>
            <code><?= htmlspecialchars(substr(GOOGLE_CLIENT_SECRET, 0, 20)) ?>...</code></p>
            
            <p><strong>Redirect URI:</strong><br>
            <code><?= htmlspecialchars(GOOGLE_REDIRECT_URI) ?></code></p>
        </div>

        <div class="info-box">
            <h3>📋 Checklist:</h3>
            <ul style="text-align: left; line-height: 2;">
                <li class="success">✅ Client ID đã cấu hình</li>
                <li class="success">✅ Client Secret đã cấu hình</li>
                <li class="success">✅ Redirect URI đã cấu hình</li>
                <li class="success">✅ File oauth-callback.php <?= file_exists(__DIR__ . '/oauth-callback.php') ? 'tồn tại' : 'KHÔNG tồn tại' ?></li>
            </ul>
        </div>

        <div class="info-box" style="background: #fff3cd; border-left: 4px solid #ffc107;">
            <h3>⚠️ Quan trọng:</h3>
            <p>Đảm bảo trong Google Cloud Console có Redirect URI:</p>
            <code style="display: block; margin: 10px 0;">http://localhost:8000/customer/oauth-callback.php</code>
            <p><small>Không có space, không có trailing slash!</small></p>
        </div>

        <div style="margin-top: 30px;">
            <a href="<?= htmlspecialchars(getGoogleAuthUrl() . '&state=google') ?>" class="btn btn-success">
                🚀 Test Đăng Nhập Google Ngay
            </a>
            <br>
            <a href="verify-oauth-credentials.php" class="btn">
                📋 Xem Chi Tiết
            </a>
            <a href="login.php" class="btn">
                🔑 Trang Đăng Nhập
            </a>
        </div>

        <div class="info-box" style="margin-top: 30px; font-size: 0.9em;">
            <h4>🐛 Nếu gặp lỗi:</h4>
            <ol style="text-align: left;">
                <li>Clear cache browser (Ctrl+Shift+Del)</li>
                <li>Kiểm tra Google Console có đúng Redirect URI</li>
                <li>Đợi 5-10 phút sau khi save trên Google Console</li>
                <li>Restart PHP server (Ctrl+C, rồi <code>php -S localhost:8000</code>)</li>
            </ol>
        </div>
    </div>
</body>
</html>
