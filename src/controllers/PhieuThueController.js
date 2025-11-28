class PhieuThueController {
    constructor(phieuThueService, phongService, thamSo) {
        this.phieuThueService = phieuThueService;
        this.phongService = phongService;
        this.thamSo = thamSo;
    }

    hienThiDanhSachPhieu() {
        const danhSach = this.phieuThueService.layDanhSachPhieu();
        return danhSach;
    }

    hienThiFormThemPhieu() {
        const phongTrong = this.phongService.layPhongTrong();
        const thamSo = this.thamSo.getAll();

        return {
            danhSachPhong: phongTrong,
            loaiKhach: thamSo.loaiKhach,
            soKhachToiDa: thamSo.soKhachToiDa
        };
    }

    // YC2: Lập phiếu thuê phòng (BM2)
    lapPhieuThue(formData) {
        try {
            const { maPhong, ngayBatDau, danhSachKhach } = formData;

            // Validate
            if (!maPhong || !ngayBatDau) {
                throw new Error('Vui lòng nhập đầy đủ thông tin phòng và ngày thuê');
            }

            if (!danhSachKhach || danhSachKhach.length === 0) {
                throw new Error('Vui lòng nhập thông tin khách hàng');
            }

            // Validate từng khách hàng
            for (let i = 0; i < danhSachKhach.length; i++) {
                const khach = danhSachKhach[i];
                if (!khach.tenKhach || !khach.loaiKhach || !khach.cmnd || !khach.diaChi) {
                    throw new Error(`Vui lòng nhập đầy đủ thông tin khách ${i + 1}`);
                }
            }

            const result = this.phieuThueService.lapPhieuThue({
                maPhong,
                ngayBatDau,
                danhSachKhach
            });

            return result;
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    layPhieuDangThue() {
        return this.phieuThueService.layPhieuDangThue();
    }

    layChiTietPhieu(maPhieuThue) {
        const phieu = this.phieuThueService.layPhieuThue(maPhieuThue);
        if (!phieu) {
            return {
                success: false,
                message: 'Không tìm thấy phiếu thuê'
            };
        }

        const phong = this.phongService.layPhong(phieu.maPhong);

        return {
            success: true,
            data: {
                phieu,
                phong
            }
        };
    }
}
