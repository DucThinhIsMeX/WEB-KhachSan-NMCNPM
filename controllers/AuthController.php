<?php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($tenDangNhap, $matKhau) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM NGUOIDUNG WHERE TenDangNhap = ? AND TrangThai = 'Hoạt động'");
            $stmt->execute([$tenDangNhap]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($matKhau, $user['MatKhau'])) {
                $_SESSION['user_id'] = $user['MaNguoiDung'];
                $_SESSION['username'] = $user['TenDangNhap'];
                $_SESSION['fullname'] = $user['HoTen'];
                $_SESSION['role'] = $user['VaiTro'];
                $_SESSION['logged_in'] = true;
                
                return ['success' => true, 'message' => 'Đăng nhập thành công!'];
            }
            
            return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng!'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'Admin';
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: login.php?error=access_denied');
            exit;
        }
    }
    
    // Quản lý tài khoản
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT * FROM NGUOIDUNG ORDER BY MaNguoiDung DESC");
        return $stmt->fetchAll();
    }
    
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM NGUOIDUNG WHERE MaNguoiDung = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createUser($username, $password, $fullname, $role = 'NhanVien') {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO NGUOIDUNG (TenDangNhap, MatKhau, HoTen, VaiTro) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $fullname, $role]);
            return ['success' => true, 'message' => 'Tạo tài khoản thành công!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    public function updateUser($id, $fullname, $role, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE NGUOIDUNG SET HoTen = ?, VaiTro = ?, TrangThai = ? WHERE MaNguoiDung = ?");
            $stmt->execute([$fullname, $role, $status, $id]);
            return ['success' => true, 'message' => 'Cập nhật thành công!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    public function changePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE NGUOIDUNG SET MatKhau = ? WHERE MaNguoiDung = ?");
            $stmt->execute([$hashedPassword, $id]);
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    public function deleteUser($id) {
        try {
            // Không cho xóa tài khoản đang đăng nhập
            if ($id == $_SESSION['user_id']) {
                return ['success' => false, 'message' => 'Không thể xóa tài khoản đang đăng nhập!'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM NGUOIDUNG WHERE MaNguoiDung = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Xóa tài khoản thành công!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
}
?>
