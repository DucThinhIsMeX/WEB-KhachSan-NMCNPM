<?php
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
            max-width: 700px; 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #1877f2; margin-bottom: 20px; }
        .info-box { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 20px 0;
            border-left: 4px solid #1877f2;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        code { 
            background: #e9ecef; 
            padding: 3px 8px; 
            border-radius: 4px;
            font-size: 0.9em;
            word-break: break-all;
            display: block;
            margin: 5px 0;
        }
        .btn { 
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #1877f2 0%, #0d65d9 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin: 10px 5px;
            transition: 0.3s;
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(24, 119, 242, 0.4);
        }
        ul { line-height: 2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔵 Test Facebook OAuth</h1>
        
        <div class="info-box">
            <h3>✅ Facebook App Configuration:</h3>
            <p><strong>App ID:</strong><br>
            <code><?= htmlspecialchars(FACEBOOK_APP_ID) ?></code></p>
            
            <p><strong>App Secret:</strong><br>
            <code><?= htmlspecialchars(substr(FACEBOOK_APP_SECRET, 0, 20)) ?>...</code></p>
            
            <p><strong>Redirect URI:</strong><br>
            <code><?= htmlspecialchars(FACEBOOK_REDIRECT_URI) ?></code></p>
        </div>

        <div class="info-box">
            <h3>📋 Checklist Facebook Developers:</h3>
            <ol>
                <li class="<?= !empty(FACEBOOK_APP_ID) ? 'success' : 'error' ?>">
                    <?= !empty(FACEBOOK_APP_ID) ? '✅' : '❌' ?> App ID đã cấu hình
                </li>
                <li class="<?= !empty(FACEBOOK_APP_SECRET) ? 'success' : 'error' ?>">
                    <?= !empty(FACEBOOK_APP_SECRET) ? '✅' : '❌' ?> App Secret đã cấu hình
                </li>
                <li>Trong Facebook App Settings → Cơ bản:</li>
                <ul>
                    <li>Tên miền ứng dụng: <code>localhost</code></li>
                    <li>URL chính sách quyền riêng tư: (có thể để trống dev)</li>
                    <li>URL điều khoản dịch vụ: (có thể để trống dev)</li>
                </ul>
                <li>Trong Facebook Login → Settings:</li>
                <ul>
                    <li>Valid OAuth Redirect URIs: <code><?= FACEBOOK_REDIRECT_URI ?></code></li>
                </ul>
                <li>App Mode: <strong>Development</strong> (để test với tài khoản dev)</li>
            </ol>
        </div>

        <div class="info-box" style="background: #fff3cd; border-left-color: #ffc107;">
            <h3>⚠️ Quan trọng:</h3>
            <ul>
                <li>Facebook App phải ở chế độ <strong>Development</strong></li>
                <li>Thêm tài khoản test vào App Roles → Roles</li>
                <li>Hoặc thêm email vào App Roles → Test Users</li>
                <li>Chỉ tài khoản được thêm mới đăng nhập được trong dev mode</li>
            </ul>
        </div>

        <div class="info-box">
            <h3>🌐 Authorization URL:</h3>
            <textarea style="width:100%;height:100px;padding:10px;font-family:monospace;font-size:0.85em;" readonly><?= htmlspecialchars(getFacebookAuthUrl()) ?></textarea>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?= htmlspecialchars(getFacebookAuthUrl()) ?>" class="btn">
                🚀 Test Đăng Nhập Facebook
            </a>
            <br>
            <a href="login.php" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                🔑 Trang Đăng Nhập
            </a>
        </div>

        <div class="info-box" style="margin-top: 30px; font-size: 0.9em;">
            <h4>🐛 Troubleshooting:</h4>
            <ol>
                <li><strong>Lỗi "App Not Setup":</strong> Cấu hình Facebook Login trong App Dashboard</li>
                <li><strong>Lỗi "Redirect URI Mismatch":</strong> Check URI trong Facebook Login Settings</li>
                <li><strong>Lỗi "Can't Load URL":</strong> 
                    <ul>
                        <li>Domain phải là localhost (không dùng 127.0.0.1)</li>
                        <li>Thêm localhost vào App Domains</li>
                    </ul>
                </li>
                <li><strong>Lỗi "This app is in development mode":</strong> Thêm tài khoản vào App Roles</li>
            </ol>
        </div>
    </div>
</body>
</html>
