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
    <title>Đăng Nhập Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            background: #fee;
            border-left: 4px solid #f00;
            color: #c00;
        }
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .login-footer {
            padding: 25px 30px;
            background: #f8f9fa;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="ph-fill ph-buildings" style="font-size: 3em; color: #667eea;"></i>
                <h1><i class="ph ph-lock-key"></i> Đăng Nhập Quản Trị</h1>
                <p>Hệ thống Quản lý Khách sạn</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i> Tên đăng nhập hoặc mật khẩu không đúng!
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label><i class="ph ph-user"></i> Tên đăng nhập</label>
                    <input type="text" name="username" required 
                           placeholder="Nhập tên đăng nhập">
                </div>

                <div class="form-group">
                    <label><i class="ph ph-lock-key"></i> Mật khẩu</label>
                    <input type="password" name="password" required 
                           placeholder="Nhập mật khẩu">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="ph ph-sign-in"></i> Đăng Nhập
                </button>
            </form>

            <div class="login-footer">
                <a href="../index.php">
                    <i class="ph ph-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
