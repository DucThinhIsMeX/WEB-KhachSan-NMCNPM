class PhongController {
    constructor(phongService, thamSo) {
        this.phongService = phongService;
        this.thamSo = thamSo;
    }

    // YC1: Lập danh mục phòng (BM1)
    hienThiDanhSachPhong() {
        const danhSach = this.phongService.layDanhSachPhong();
        return danhSach;
    }

    hienThiFormThemPhong() {
        const thamSo = this.thamSo.getAll();
        return {
            loaiPhong: Object.keys(thamSo.donGiaPhong),
            donGiaPhong: thamSo.donGiaPhong
        };
    }

    themPhong(formData) {
        try {
            const { maPhong, tenPhong, loaiPhong, donGia, ghiChu } = formData;

            // Validate
            if (!maPhong || !tenPhong || !loaiPhong) {
                throw new Error('Vui lòng nhập đầy đủ thông tin bắt buộc');
            }

            const phong = new Phong(
                maPhong.trim(),
                tenPhong.trim(),
                loaiPhong,
                parseFloat(donGia),
                ghiChu ? ghiChu.trim() : ''
            );

            this.phongService.themPhong(phong);

            return {
                success: true,
                message: 'Thêm phòng thành công',
                data: phong
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    capNhatPhong(maPhong, formData) {
        try {
            const phong = this.phongService.capNhatPhong(maPhong, formData);
            return {
                success: true,
                message: 'Cập nhật phòng thành công',
                data: phong
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    xoaPhong(maPhong) {
        try {
            this.phongService.xoaPhong(maPhong);
            return {
                success: true,
                message: 'Xóa phòng thành công'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // YC3: Tra cứu phòng (BM3)
    traCuuPhong(criteria) {
        const result = this.phongService.traCuuPhong(criteria);
        return {
            success: true,
            data: result
        };
    }

    locPhong(loaiPhong, trangThai) {
        const filter = {};
        if (loaiPhong) filter.loaiPhong = loaiPhong;
        if (trangThai) filter.trangThai = trangThai;

        return this.phongService.layDanhSachPhong(filter);
    }
}
