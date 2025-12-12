<?php
require_once __DIR__ . '/../config/database.php';

class KhachHangController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Thêm khách hàng
    public function themKhachHang($tenKhach, $loaiKhach, $cmnd, $diaChi) {
        $stmt = $this->db->prepare("INSERT INTO KHACHHANG (TenKhach, LoaiKhach, CMND, DiaChi) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tenKhach, $loaiKhach, $cmnd, $diaChi]);
        return $this->db->lastInsertId();
    }
    
    // Lấy danh sách khách hàng
    public function getAllKhachHang() {
        $stmt = $this->db->query("SELECT * FROM KHACHHANG ORDER BY MaKhachHang DESC");
        return $stmt->fetchAll();
    }
    
    // Tìm kiếm khách hàng
    public function timKhachHang($keyword) {
        $stmt = $this->db->prepare("SELECT * FROM KHACHHANG 
                                    WHERE TenKhach LIKE ? OR CMND LIKE ?");
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetchAll();
    }
}
?>
