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
public function taoPhieuThue($maPhong, $ngayBatDau, $soDem, $danhSachKhach, $maKhachHangUser = null) {

try {
$this->db->beginTransaction();
$soDem = (int)$soDem;
$soDem = max(1, min(14, $soDem));

// Kiểm tra số lượng khách tối đa
$soKhachToiDa = $this->database->getThamSo('SO_KHACH_TOI_DA');
if ($soKhachToiDa === null) {
$soKhachToiDa = 3; // Giá trị mặc định
}
if (count($danhSachKhach) > $soKhachToiDa) {
throw new Exception("Số lượng khách vượt quá quy định ($soKhachToiDa khách)");
}
// Kiểm tra phòng có trống không
$stmt = $this->db->prepare("SELECT TinhTrang FROM PHONG WHERE MaPhong = ?");
$stmt->execute([$maPhong]);
$phong = $stmt->fetch();
if (!$phong) {
throw new Exception("Phòng không tồn tại");
}
if ($phong['TinhTrang'] !== 'Trống') {
throw new Exception("Phòng đã được thuê");
}
// Tạo phiếu thuê
$stmt = $this->db->prepare("INSERT INTO PHIEUTHUE (MaPhong, MaKhachHangUser, NgayBatDauThue, SoDem, TinhTrangPhieu) 
VALUES (?, ?, ?, ?, 'Đang thuê')");
$stmt->execute([$maPhong, $maKhachHangUser, $ngayBatDau, $soDem]);
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

// Lấy chi tiết phiếu thuê theo mã
public function getPhieuThueById($maPhieuThue) {
$stmt = $this->db->prepare("
SELECT PT.MaPhieuThue, PT.MaPhong, PT.NgayBatDauThue, PT.SoDem, PT.TinhTrangPhieu,
       P.SoPhong, P.MaPhong, L.TenLoai, L.DonGiaCoBan, L.MaLoaiPhong
FROM PHIEUTHUE PT
JOIN PHONG P ON PT.MaPhong = P.MaPhong
JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong
WHERE PT.MaPhieuThue = ?
");
$stmt->execute([$maPhieuThue]);
return $stmt->fetch();
}

// Cập nhật phiếu thuê
public function capNhatPhieuThue($maPhieuThue, $maPhong, $ngayBatDau, $soDem, $danhSachKhach) {
try {
$this->db->beginTransaction();

// Kiểm tra phiếu thuê tồn tại và chưa thanh toán
$stmt = $this->db->prepare("SELECT TinhTrangPhieu, MaPhong FROM PHIEUTHUE WHERE MaPhieuThue = ?");
$stmt->execute([$maPhieuThue]);
$phieuThue = $stmt->fetch();

if (!$phieuThue) {
throw new Exception("Không tìm thấy phiếu thuê");
}

if ($phieuThue['TinhTrangPhieu'] !== 'Đang thuê') {
throw new Exception("Chỉ có thể sửa phiếu thuê đang hoạt động");
}

// Validate số khách
$soKhachToiDa = intval($this->database->getThamSo('SO_KHACH_TOI_DA'));
if (count($danhSachKhach) > $soKhachToiDa) {
throw new Exception("Số khách vượt quá quy định ($soKhachToiDa khách/phòng)");
}

// Nếu đổi phòng, cần kiểm tra phòng mới
if ($phieuThue['MaPhong'] != $maPhong) {
// Kiểm tra phòng mới có trống không
$stmt = $this->db->prepare("SELECT TinhTrang FROM PHONG WHERE MaPhong = ?");
$stmt->execute([$maPhong]);
$phongMoi = $stmt->fetch();

if (!$phongMoi || $phongMoi['TinhTrang'] !== 'Trống') {
throw new Exception("Phòng mới không khả dụng");
}

// Trả phòng cũ về trạng thái trống
$stmt = $this->db->prepare("UPDATE PHONG SET TinhTrang = 'Trống' WHERE MaPhong = ?");
$stmt->execute([$phieuThue['MaPhong']]);

                // Đánh dấu phòng mới là đã thuê
                $stmt = $this->db->prepare("UPDATE PHONG SET TinhTrang = 'Đã thuê' WHERE MaPhong = ?");
                $stmt->execute([$maPhong]);
            }

            // Cập nhật phiếu thuê
            $stmt = $this->db->prepare("
                UPDATE PHIEUTHUE 
                SET MaPhong = ?, NgayBatDauThue = ?, SoDem = ?
                WHERE MaPhieuThue = ?
            ");
            $stmt->execute([$maPhong, $ngayBatDau, $soDem, $maPhieuThue]);

            // Xóa chi tiết khách cũ
            $stmt = $this->db->prepare("DELETE FROM CHITIET_THUE WHERE MaPhieuThue = ?");
            $stmt->execute([$maPhieuThue]);

            // Thêm lại chi tiết khách mới
            foreach ($danhSachKhach as $khach) {
                // Kiểm tra khách hàng đã tồn tại chưa
                $stmt = $this->db->prepare("SELECT MaKhachHang FROM KHACHHANG WHERE CMND = ?");
                $stmt->execute([$khach['cmnd']]);
                $maKH = $stmt->fetchColumn();

                if ($maKH) {
                    // Cập nhật thông tin khách hàng
                    $stmt = $this->db->prepare("
                        UPDATE KHACHHANG 
                        SET TenKhach = ?, LoaiKhach = ?, DiaChi = ?
                        WHERE MaKhachHang = ?
                    ");
                    $stmt->execute([
                        $khach['tenKhach'],
                        $khach['loaiKhach'],
                        $khach['diaChi'],
                        $maKH
                    ]);
                } else {
                    // Thêm khách hàng mới
                    $stmt = $this->db->prepare("
                        INSERT INTO KHACHHANG (TenKhach, LoaiKhach, CMND, DiaChi) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $khach['tenKhach'],
                        $khach['loaiKhach'],
                        $khach['cmnd'],
                        $khach['diaChi']
                    ]);
                    $maKH = $this->db->lastInsertId();
                }

                // Thêm chi tiết thuê
                $stmt = $this->db->prepare("INSERT INTO CHITIET_THUE (MaPhieuThue, MaKhachHang) VALUES (?, ?)");
                $stmt->execute([$maPhieuThue, $maKH]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
?>