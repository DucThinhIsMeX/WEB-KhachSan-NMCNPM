class HoaDonController {
    constructor(hoaDonService, phieuThueService) {
        this.hoaDonService = hoaDonService;
        this.phieuThueService = phieuThueService;
    }

    hienThiDanhSachHoaDon() {
        const danhSach = this.hoaDonService.layDanhSachHoaDon({});
        return danhSach;
    }

    hienThiFormTaoHoaDon() {
        const phieuDangThue = this.phieuThueService.layPhieuDangThue();
        return {
            danhSachPhieu: phieuDangThue
        };
    }

    // YC4: Lập hóa đơn thanh toán (BM4)
    lapHoaDon(formData) {
        try {
            const { maPhieuThue, khachHang, diaChi, ngayKetThuc } = formData;

            // Validate
            if (!maPhieuThue || !khachHang || !diaChi) {
                throw new Error('Vui lòng nhập đầy đủ thông tin');
            }

            const result = this.hoaDonService.lapHoaDon({
                maPhieuThue,
                khachHang: khachHang.trim(),
                diaChi: diaChi.trim(),
                ngayKetThuc: ngayKetThuc || new Date().toISOString().split('T')[0]
            });

            return result;
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    tinhChiTietThanhToan(maPhieuThue) {
        try {
            const chiTiet = this.hoaDonService.tinhChiTietThanhToan(maPhieuThue);
            if (!chiTiet) {
                throw new Error('Không tìm thấy phiếu thuê');
            }

            return {
                success: true,
                data: chiTiet
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    layChiTietHoaDon(maHoaDon) {
        const hoaDon = this.hoaDonService.layHoaDon(maHoaDon);
        if (!hoaDon) {
            return {
                success: false,
                message: 'Không tìm thấy hóa đơn'
            };
        }

        return {
            success: true,
            data: hoaDon
        };
    }
}
