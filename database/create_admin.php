<?php
require_once __DIR__ . '/../config/database.php';

// Tắt hiển thị lỗi chi tiết
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->connect();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Tài Khoản Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .success h3 {
            color: #28a745;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .error h3 {
            color: #dc3545;
        }
        .info-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        .info-box h2 {
            color: #856404;
            margin-bottom: 20px;
        }
        .credentials {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials p {
            font-size: 1.1em;
            margin: 10px 0;
        }
        .credentials code {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        ul li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Tạo Tài Khoản Admin</h1>
            <p>Khởi tạo hệ thống đăng nhập</p>
        </div>
        
        <div class="content">
            <?php
            $success = true;
            $messages = [];
            
            try {
                // Bước 1: Kiểm tra và tạo bảng NGUOIDUNG
                echo '<div class="step">';
                echo '<h3>📋 Bước 1: Kiểm tra bảng NGUOIDUNG</h3>';
                
                $stmt = $db->query("SHOW TABLES LIKE 'NGUOIDUNG'");
                $tableExists = $stmt->rowCount() > 0;
                
                if (!$tableExists) {
                    echo '<p>⏳ Bảng chưa tồn tại, đang tạo mới...</p>';
                    
                    $sql = "CREATE TABLE NGUOIDUNG (
                        MaNguoiDung INT AUTO_INCREMENT PRIMARY KEY,
                        TenDangNhap VARCHAR(50) UNIQUE NOT NULL,
                        MatKhau VARCHAR(255) NOT NULL,
                        HoTen VARCHAR(100) NOT NULL,
                        VaiTro ENUM('Admin', 'NhanVien') DEFAULT 'NhanVien',
                        TrangThai ENUM('Hoạt động', 'Khóa') DEFAULT 'Hoạt động',
                        NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                    
                    $db->exec($sql);
                    echo '<p>✅ Tạo bảng NGUOIDUNG thành công!</p>';
                    $messages[] = 'Tạo bảng NGUOIDUNG thành công';
                } else {
                    echo '<p>✅ Bảng NGUOIDUNG đã tồn tại</p>';
                    $messages[] = 'Bảng NGUOIDUNG đã tồn tại';
                }
                echo '</div>';
                
                // Bước 2: Kiểm tra tài khoản admin
                echo '<div class="step">';
                echo '<h3>👤 Bước 2: Tạo tài khoản admin</h3>';
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM NGUOIDUNG WHERE TenDangNhap = 'admin'");
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    echo '<p>⏳ Đang tạo tài khoản admin...</p>';
                    
                    // Tạo mật khẩu đã mã hóa
                    $username = 'admin';
                    $password = 'admin123';
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $db->prepare("INSERT INTO NGUOIDUNG (TenDangNhap, MatKhau, HoTen, VaiTro, TrangThai) 
                                         VALUES (?, ?, ?, 'Admin', 'Hoạt động')");
                    $stmt->execute([$username, $hashedPassword, 'Quản Trị Viên']);
                    
                    echo '<p>✅ Tạo tài khoản admin thành công!</p>';
                    $messages[] = 'Tạo tài khoản admin mới';
                } else {
                    echo '<p>ℹ️ Tài khoản admin đã tồn tại</p>';
                    $messages[] = 'Tài khoản admin đã tồn tại';
                }
                echo '</div>';
                
                // Bước 3: Tạo thêm tài khoản demo (tùy chọn)
                echo '<div class="step">';
                echo '<h3>👥 Bước 3: Tạo tài khoản demo</h3>';
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM NGUOIDUNG WHERE TenDangNhap = 'demo'");
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    $demoPassword = password_hash('demo123', PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO NGUOIDUNG (TenDangNhap, MatKhau, HoTen, VaiTro, TrangThai) 
                                         VALUES (?, ?, ?, 'NhanVien', 'Hoạt động')");
                    $stmt->execute(['demo', $demoPassword, 'Nhân Viên Demo']);
                    
                    echo '<p>✅ Tạo tài khoản demo thành công!</p>';
                    $messages[] = 'Tạo tài khoản demo (NhanVien)';
                } else {
                    echo '<p>ℹ️ Tài khoản demo đã tồn tại</p>';
                }
                echo '</div>';
                
                // Hiển thị danh sách tài khoản
                echo '<div class="step success">';
                echo '<h3>✅ Bước 4: Danh sách tài khoản hiện có</h3>';
                
                $stmt = $db->query("SELECT TenDangNhap, HoTen, VaiTro, TrangThai, 
                                    DATE_FORMAT(NgayTao, '%d/%m/%Y %H:%i') as NgayTaoFormat 
                                    FROM NGUOIDUNG ORDER BY MaNguoiDung");
                $users = $stmt->fetchAll();
                
                echo '<ul>';
                foreach ($users as $user) {
                    echo '<li>';
                    echo '<strong>' . htmlspecialchars($user['TenDangNhap']) . '</strong> - ';
                    echo htmlspecialchars($user['HoTen']) . ' ';
                    echo '(' . $user['VaiTro'] . ') - ';
                    echo $user['TrangThai'] . ' - ';
                    echo 'Tạo lúc: ' . $user['NgayTaoFormat'];
                    echo '</li>';
                }
                echo '</ul>';
                
                echo '</div>';
                
            } catch(PDOException $e) {
                $success = false;
                echo '<div class="step error">';
                echo '<h3>❌ Lỗi</h3>';
                echo '<p><strong>Chi tiết:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p>Vui lòng kiểm tra:</p>';
                echo '<ul>';
                echo '<li>MySQL/XAMPP đã được khởi động</li>';
                echo '<li>Thông tin kết nối database trong config/database.php</li>';
                echo '<li>Quyền truy cập database</li>';
                echo '</ul>';
                echo '</div>';
            }
            
            if ($success):
            ?>
            
            <!-- Thông tin đăng nhập -->
            <div class="info-box">
                <h2>🎉 Khởi Tạo Thành Công!</h2>
                
                <div class="credentials">
                    <p><strong>🔐 TÀI KHOẢN ADMIN:</strong></p>
                    <p>Username: <code>admin</code></p>
                    <p>Password: <code>admin123</code></p>
                </div>
                
                <div class="credentials" style="background: #f0f4ff;">
                    <p><strong>👤 TÀI KHOẢN DEMO (Nhân Viên):</strong></p>
                    <p>Username: <code>demo</code></p>
                    <p>Password: <code>demo123</code></p>
                </div>
                
                <p style="margin-top: 20px; color: #dc3545; font-weight: bold;">
                    ⚠️ Lưu ý: Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu!
                </p>
            </div>
            
            <!-- Nút điều hướng -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="../admin/login.php" class="btn btn-success">
                    🚀 Đăng Nhập Admin
                </a>
                <a href="../admin/tai-khoan.php" class="btn">
                    👥 Quản Lý Tài Khoản
                </a>
                <a href="../index.php" class="btn">
                    🏠 Trang Chủ
                </a>
            </div>
            
            <?php else: ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="../test_database.php" class="btn">
                    🔧 Kiểm Tra Kết Nối
                </a>
                <a href="../index.php" class="btn">
                    🏠 Trang Chủ
                </a>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
