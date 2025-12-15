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
    <title>Đăng Nhập</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box { 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
        }
        h1 { 
            text-align: center; 
            color: #333; 
            margin-bottom: 30px; 
        }
        .btn { 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-google { 
            background: white; 
            color: #333; 
            border: 2px solid #ddd; 
        }
        .btn-google:hover { 
            border-color: #4285f4; 
            box-shadow: 0 4px 12px rgba(66,133,244,0.3); 
        }
        .btn-facebook { 
            background: #1877f2; 
            color: white; 
        }
        .btn-facebook:hover { 
            background: #0d65d9; 
            box-shadow: 0 4px 12px rgba(24,119,242,0.3); 
        }
        .btn-admin {
            background: #2c3e50;
            color: white;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .btn-admin:hover {
            background: #34495e;
        }
        .error { 
            background: #fee; 
            color: #c00; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            border-left: 4px solid #f00;
        }
        .back { 
            text-align: center; 
            margin-top: 20px; 
        }
        .back a { 
            color: #667eea; 
            text-decoration: none; 
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Đăng Nhập</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <a href="<?= htmlspecialchars($googleUrl) ?>" class="btn btn-google">
            <i class="ph ph-google-logo" style="font-size: 1.5em;"></i>
            Đăng nhập bằng Google
        </a>
        
        <a href="<?= htmlspecialchars($facebookUrl) ?>" class="btn btn-facebook">
            <i class="ph ph-facebook-logo" style="font-size: 1.5em;"></i>
            Đăng nhập bằng Facebook
        </a>
        
        <a href="../admin/login.php" class="btn btn-admin">
            <i class="ph-fill ph-shield-check" style="font-size: 1.5em;"></i>
            Đăng nhập quản trị
        </a>
        
        <div class="back">
            <a href="../index.php">← Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html>
