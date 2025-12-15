<?php
require_once __DIR__ . '/../config/database.php';

class PhongController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Tra cứu phòng (YCC 3)
    public function traCuuPhong($loaiPhong = null, $tinhTrang = 'Trống') {
        $sql = "SELECT P.*, L.TenLoai, L.DonGiaCoBan 
                FROM PHONG P 
                JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                WHERE P.TinhTrang = ?";
        
        $params = [$tinhTrang];
        
        if ($loaiPhong) {
            $sql .= " AND P.MaLoaiPhong = ?";
            $params[] = $loaiPhong;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Lấy tất cả phòng
    public function getAllPhong() {
        $stmt = $this->db->query("SELECT P.*, L.TenLoai, L.DonGiaCoBan 
                                  FROM PHONG P 
                                  JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong");
        return $stmt->fetchAll();
    }
    
    // Thêm phòng mới (BM1)
    public function themPhong($soPhong, $maLoaiPhong, $ghiChu = null) {
        $stmt = $this->db->prepare("INSERT INTO PHONG (SoPhong, MaLoaiPhong, TinhTrang, GhiChu) 
                                    VALUES (?, ?, 'Trống', ?)");
        return $stmt->execute([$soPhong, $maLoaiPhong, $ghiChu]);
    }
    
    // Cập nhật thông tin phòng (BM1)
    public function capNhatPhong($maPhong, $soPhong, $maLoaiPhong, $ghiChu = null) {
        $stmt = $this->db->prepare("UPDATE PHONG SET SoPhong = ?, MaLoaiPhong = ?, GhiChu = ? 
                                    WHERE MaPhong = ?");
        return $stmt->execute([$soPhong, $maLoaiPhong, $ghiChu, $maPhong]);
    }
    
    // Cập nhật tình trạng phòng (BM3)
    public function capNhatTinhTrang($maPhong, $tinhTrang) {
        $stmt = $this->db->prepare("UPDATE PHONG SET TinhTrang = ? WHERE MaPhong = ?");
        return $stmt->execute([$tinhTrang, $maPhong]);
    }
    
    // Xóa phòng
    public function xoaPhong($maPhong) {
        $stmt = $this->db->prepare("DELETE FROM PHONG WHERE MaPhong = ?");
        return $stmt->execute([$maPhong]);
    }
}
?>
