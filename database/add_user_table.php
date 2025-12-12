<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Bảng Người Dùng</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 5px; }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #5568d3; }
        code { background: #fff3cd; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Thêm Bảng NGUOIDUNG</h1>
        
        <?php
        try {
            // Kiểm tra bảng tồn tại
            $stmt = $db->query("SHOW TABLES LIKE 'NGUOIDUNG'");
            $exists = $stmt->rowCount() > 0;
            
            if (!$exists) {
                echo '<div class="step">Đang tạo bảng NGUOIDUNG...</div>';
                
                $db->exec("CREATE TABLE NGUOIDUNG (
                    MaNguoiDung INT AUTO_INCREMENT PRIMARY KEY,
                    TenDangNhap VARCHAR(50) UNIQUE NOT NULL,
                    MatKhau VARCHAR(255) NOT NULL,
                    HoTen VARCHAR(100) NOT NULL,
                    VaiTro ENUM('Admin', 'NhanVien') DEFAULT 'NhanVien',
                    TrangThai ENUM('Hoạt động', 'Khóa') DEFAULT 'Hoạt động',
                    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                
                echo '<div class="step success">✅ Tạo bảng thành công!</div>';
                
                // Thêm tài khoản admin
                $matKhau = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO NGUOIDUNG (TenDangNhap, MatKhau, HoTen, VaiTro) VALUES (?, ?, ?, ?)");
                $stmt->execute(['admin', $matKhau, 'Quản Trị Viên', 'Admin']);
                
                echo '<div class="step success">✅ Tạo tài khoản admin thành công!</div>';
            } else {
                echo '<div class="step">ℹ️ Bảng NGUOIDUNG đã tồn tại!</div>';
            }
            
            echo '<div class="step success">';
            echo '<h3>📌 Thông tin đăng nhập:</h3>';
            echo '<p>Username: <code>admin</code></p>';
            echo '<p>Password: <code>admin123</code></p>';
            echo '</div>';
            
            echo '<a href="../admin/login.php" class="btn">🔐 Đăng Nhập</a>';
            echo '<a href="../admin/tai-khoan.php" class="btn">👥 Quản Lý Tài Khoản</a>';
            
        } catch(PDOException $e) {
            echo '<div class="step error">❌ Lỗi: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</body>
</html>
