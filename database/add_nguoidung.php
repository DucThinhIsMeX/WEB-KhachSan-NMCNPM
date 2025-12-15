<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

try {
    echo "<!DOCTYPE html>";
    echo "<html><head><meta charset='UTF-8'><title>Thêm Bảng NGUOIDUNG</title>";
    echo "<script src='https://unpkg.com/@phosphor-icons/web'></script>";
    echo "</head><body>";
    echo "<h2><i class='ph ph-wrench'></i> Thêm Bảng NGUOIDUNG</h2>";
    
    // Tạo bảng NGUOIDUNG
    echo "<p>Đang tạo bảng NGUOIDUNG...</p>";
    $db->exec("CREATE TABLE IF NOT EXISTS NGUOIDUNG (
        MaNguoiDung INT AUTO_INCREMENT PRIMARY KEY,
        TenDangNhap VARCHAR(50) UNIQUE NOT NULL,
        MatKhau VARCHAR(255) NOT NULL,
        HoTen VARCHAR(100) NOT NULL,
        VaiTro ENUM('Admin', 'NhanVien') DEFAULT 'NhanVien',
        TrangThai ENUM('Hoạt động', 'Khóa') DEFAULT 'Hoạt động',
        NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p style='color: green;'>✅ Tạo bảng NGUOIDUNG thành công!</p>";
    
    // Kiểm tra xem đã có tài khoản admin chưa
    $stmt = $db->query("SELECT COUNT(*) as count FROM NGUOIDUNG WHERE TenDangNhap = 'admin'");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "<p>Đang tạo tài khoản admin mặc định...</p>";
        $matKhauMaHoa = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO NGUOIDUNG (TenDangNhap, MatKhau, HoTen, VaiTro) 
                             VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $matKhauMaHoa, 'Quản Trị Viên', 'Admin']);
        echo "<p style='color: green;'>✅ Tạo tài khoản admin thành công!</p>";
    } else {
        echo "<p style='color: orange;'>ℹ️ Tài khoản admin đã tồn tại.</p>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'><i class='ph ph-check-circle'></i> Hoàn Tất!</h3>";
    echo "<div style='background: #f0f4ff; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0;'>";
    echo "<p><strong><i class='ph ph-info'></i> Tài khoản admin mặc định:</strong></p>";
    echo "<ul>";
    echo "<li>Tên đăng nhập: <code style='background: #fff; padding: 2px 6px; border-radius: 3px;'>admin</code></li>";
    echo "<li>Mật khẩu: <code style='background: #fff; padding: 2px 6px; border-radius: 3px;'>admin123</code></li>";
    echo "</ul>";
    echo "</div>";
    echo "<p><a href='../admin/login.php' style='color: #667eea; text-decoration: none; font-weight: bold;'><i class='ph ph-sign-in'></i> Đăng nhập Admin</a></p>";
    echo "<p><a href='../index.php' style='color: #667eea; text-decoration: none;'><i class='ph ph-house'></i> Trang chủ</a></p>";
    echo "</body></html>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'><i class='ph ph-warning'></i> Lỗi: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>Vui lòng kiểm tra kết nối database.</p>";
}
?>
