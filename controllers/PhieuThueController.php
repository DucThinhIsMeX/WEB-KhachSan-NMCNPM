<?php
require_once __DIR__ . '/../config/database.php';

class PhieuThueController {
    private $db;
    private $database;
    
    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->connect();
    }
    
    // Tạo phiếu thuê mới (YCC 2, BM2)
    public function taoPhieuThue($maPhong, $ngayBatDau, $danhSachKhach) {
        try {
            $this->db->beginTransaction();
            
            // Kiểm tra số lượng khách tối đa
            $soKhachToiDa = $this->database->getThamSo('SO_KHACH_TOI_DA');
            if (count($danhSachKhach) > $soKhachToiDa) {
                throw new Exception("Số lượng khách vượt quá quy định ($soKhachToiDa khách)");
            }
            
            // Tạo phiếu thuê
            $stmt = $this->db->prepare("INSERT INTO PHIEUTHUE (MaPhong, NgayBatDauThue, TinhTrangPhieu) 
                                       VALUES (?, ?, 'Đang thuê')");
            $stmt->execute([$maPhong, $ngayBatDau]);
            $maPhieuThue = $this->db->lastInsertId();
            
            // Thêm chi tiết khách hàng
            $stmt = $this->db->prepare("INSERT INTO CHITIET_THUE (MaPhieuThue, MaKhachHang) VALUES (?, ?)");
            foreach ($danhSachKhach as $maKhach) {
                $stmt->execute([$maPhieuThue, $maKhach]);
            }
            
            // Cập nhật tình trạng phòng
            $stmt = $this->db->prepare("UPDATE PHONG SET TinhTrang = 'Đã thuê' WHERE MaPhong = ?");
            $stmt->execute([$maPhong]);
            
            $this->db->commit();
            return $maPhieuThue;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Lấy danh sách phiếu thuê
    public function getPhieuThue($tinhTrang = null) {
        $sql = "SELECT PT.*, P.SoPhong 
                FROM PHIEUTHUE PT 
                JOIN PHONG P ON PT.MaPhong = P.MaPhong";
        
        if ($tinhTrang) {
            $sql .= " WHERE PT.TinhTrangPhieu = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tinhTrang]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    // Lấy chi tiết khách trong phiếu thuê
    public function getChiTietKhach($maPhieuThue) {
        $stmt = $this->db->prepare("SELECT K.* FROM KHACHHANG K 
                                    JOIN CHITIET_THUE CT ON K.MaKhachHang = CT.MaKhachHang 
                                    WHERE CT.MaPhieuThue = ?");
        $stmt->execute([$maPhieuThue]);
        return $stmt->fetchAll();
    }
}
?>
