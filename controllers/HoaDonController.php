<?php
require_once __DIR__ . '/../config/database.php';

class HoaDonController {
    private $db;
    private $database;
    
    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->connect();
    }
    
    // Tính đơn giá theo QĐ4
    private function tinhDonGia($maPhong, $maPhieuThue) {
        // Lấy đơn giá cơ bản
        $stmt = $this->db->prepare("SELECT L.DonGiaCoBan 
                                    FROM PHONG P 
                                    JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                                    WHERE P.MaPhong = ?");
        $stmt->execute([$maPhong]);
        $donGia = $stmt->fetchColumn();
        
        // Đếm số khách
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM CHITIET_THUE WHERE MaPhieuThue = ?");
        $stmt->execute([$maPhieuThue]);
        $soKhach = $stmt->fetchColumn();
        
        // Kiểm tra khách nước ngoài
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM CHITIET_THUE CT 
                                    JOIN KHACHHANG K ON CT.MaKhachHang = K.MaKhachHang 
                                    WHERE CT.MaPhieuThue = ? AND K.LoaiKhach = 'Nước ngoài'");
        $stmt->execute([$maPhieuThue]);
        $coKhachNN = $stmt->fetchColumn() > 0;
        
        // Áp dụng QĐ4
        $soKhachToiDa = $this->database->getThamSo('SO_KHACH_TOI_DA');
        if ($soKhach >= $soKhachToiDa) {
            $tlPhuThu = $this->database->getThamSo('TL_PHU_THU_KHACH_3');
            $donGia *= (1 + $tlPhuThu);
        }
        
        if ($coKhachNN) {
            $hsNN = $this->database->getThamSo('HS_KHACH_NUOC_NGOAI');
            $donGia *= $hsNN;
        }
        
        return $donGia;
    }
    
    // Lập hóa đơn (YCC 4, BM4)
    public function lapHoaDon($maPhieuThue, $tenKH, $diaChi, $ngayThanhToan) {
        try {
            $this->db->beginTransaction();
            
            // Lấy thông tin phiếu thuê
            $stmt = $this->db->prepare("SELECT * FROM PHIEUTHUE WHERE MaPhieuThue = ?");
            $stmt->execute([$maPhieuThue]);
            $phieuThue = $stmt->fetch();
            
            // Tính số ngày thuê
            $ngayBatDau = new DateTime($phieuThue['NgayBatDauThue']);
            $ngayKetThuc = new DateTime($ngayThanhToan);
            $soNgay = $ngayKetThuc->diff($ngayBatDau)->days;
            
            if ($soNgay == 0) $soNgay = 1; // Tối thiểu 1 ngày
            
            // Tính đơn giá theo QĐ4
            $donGiaTinh = $this->tinhDonGia($phieuThue['MaPhong'], $maPhieuThue);
            $thanhTien = $donGiaTinh * $soNgay;
            
            // Tạo hóa đơn
            $stmt = $this->db->prepare("INSERT INTO HOADON (MaPhieuThue, TenKhachHangCoQuan, DiaChiThanhToan, NgayThanhToan, TriGia) 
                                       VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$maPhieuThue, $tenKH, $diaChi, $ngayThanhToan, $thanhTien]);
            $maHoaDon = $this->db->lastInsertId();
            
            // Tạo chi tiết hóa đơn
            $stmt = $this->db->prepare("INSERT INTO CHITIET_HOADON (MaHoaDon, MaPhong, SoNgayThue, DonGiaTinh, ThanhTien) 
                                       VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$maHoaDon, $phieuThue['MaPhong'], $soNgay, $donGiaTinh, $thanhTien]);
            
            // Cập nhật tình trạng phiếu thuê
            $stmt = $this->db->prepare("UPDATE PHIEUTHUE SET TinhTrangPhieu = 'Đã thanh toán' WHERE MaPhieuThue = ?");
            $stmt->execute([$maPhieuThue]);
            
            // Cập nhật tình trạng phòng
            $stmt = $this->db->prepare("UPDATE PHONG SET TinhTrang = 'Trống' WHERE MaPhong = ?");
            $stmt->execute([$phieuThue['MaPhong']]);
            
            $this->db->commit();
            return $maHoaDon;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Lấy chi tiết hóa đơn
    public function getHoaDon($maHoaDon) {
        $stmt = $this->db->prepare("SELECT H.*, PT.NgayBatDauThue, P.SoPhong 
                                    FROM HOADON H 
                                    JOIN PHIEUTHUE PT ON H.MaPhieuThue = PT.MaPhieuThue 
                                    JOIN PHONG P ON PT.MaPhong = P.MaPhong 
                                    WHERE H.MaHoaDon = ?");
        $stmt->execute([$maHoaDon]);
        return $stmt->fetch();
    }
}
?>
