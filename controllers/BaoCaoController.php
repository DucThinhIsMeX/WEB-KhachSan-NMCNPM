<?php
require_once __DIR__ . '/../config/database.php';

class BaoCaoController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Lập báo cáo doanh thu (YCC 5, BM5)
    public function lapBaoCao($thang, $nam) {
        try {
            $this->db->beginTransaction();
            
            // Tạo báo cáo
            $stmt = $this->db->prepare("INSERT INTO BAOCAO_DOANHTHU (Thang, Nam, NgayLap) VALUES (?, ?, DATE('now'))");
            $stmt->execute([$thang, $nam]);
            $maBaoCao = $this->db->lastInsertId();
            
            // Tính doanh thu theo loại phòng
            $stmt = $this->db->query("SELECT MaLoaiPhong FROM LOAIPHONG");
            $loaiPhongs = $stmt->fetchAll();
            
            $tongDoanhThu = 0;
            $doanhThuTheoLoai = [];
            
            foreach ($loaiPhongs as $loai) {
                $sql = "SELECT COALESCE(SUM(CH.ThanhTien), 0) as DoanhThu 
                        FROM CHITIET_HOADON CH 
                        JOIN HOADON H ON CH.MaHoaDon = H.MaHoaDon 
                        JOIN PHONG P ON CH.MaPhong = P.MaPhong 
                        WHERE P.MaLoaiPhong = ? 
                        AND strftime('%m', H.NgayThanhToan) = ? 
                        AND strftime('%Y', H.NgayThanhToan) = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$loai['MaLoaiPhong'], sprintf('%02d', $thang), $nam]);
                $doanhThu = $stmt->fetchColumn();
                
                $doanhThuTheoLoai[$loai['MaLoaiPhong']] = $doanhThu;
                $tongDoanhThu += $doanhThu;
            }
            
            // Lưu chi tiết báo cáo
            $stmt = $this->db->prepare("INSERT INTO CHITIET_BAOCAO (MaBaoCao, MaLoaiPhong, DoanhThu, TyLe) VALUES (?, ?, ?, ?)");
            
            foreach ($doanhThuTheoLoai as $maLoai => $doanhThu) {
                $tyLe = $tongDoanhThu > 0 ? ($doanhThu / $tongDoanhThu) * 100 : 0;
                $stmt->execute([$maBaoCao, $maLoai, $doanhThu, $tyLe]);
            }
            
            $this->db->commit();
            return $maBaoCao;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Xem báo cáo
    public function xemBaoCao($maBaoCao) {
        $stmt = $this->db->prepare("SELECT BC.*, CB.*, L.TenLoai 
                                    FROM BAOCAO_DOANHTHU BC 
                                    JOIN CHITIET_BAOCAO CB ON BC.MaBaoCao = CB.MaBaoCao 
                                    JOIN LOAIPHONG L ON CB.MaLoaiPhong = L.MaLoaiPhong 
                                    WHERE BC.MaBaoCao = ?");
        $stmt->execute([$maBaoCao]);
        return $stmt->fetchAll();
    }
}
?>
