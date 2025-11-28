class ThamSoController {
    constructor(thamSo) {
        this.thamSo = thamSo;
    }

    layThamSo() {
        return this.thamSo.getAll();
    }

    // YC6: Thay đổi qui định (QĐ6)
    capNhatThamSo(formData) {
        try {
            // Validate
            if (formData.donGiaA && formData.donGiaA < 0) {
                throw new Error('Đơn giá phải lớn hơn 0');
            }
            if (formData.donGiaB && formData.donGiaB < 0) {
                throw new Error('Đơn giá phải lớn hơn 0');
            }
            if (formData.donGiaC && formData.donGiaC < 0) {
                throw new Error('Đơn giá phải lớn hơn 0');
            }
            if (formData.soKhachToiDa && formData.soKhachToiDa < 1) {
                throw new Error('Số khách tối đa phải lớn hơn 0');
            }
            if (formData.phuThuKhach3 && (formData.phuThuKhach3 < 0 || formData.phuThuKhach3 > 100)) {
                throw new Error('Phụ thu phải từ 0% đến 100%');
            }
            if (formData.heSoNuocNgoai && formData.heSoNuocNgoai < 1) {
                throw new Error('Hệ số phải lớn hơn hoặc bằng 1');
            }

            this.thamSo.updateThamSo(formData);

            return {
                success: true,
                message: 'Cập nhật tham số thành công'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    khoiPhucMacDinh() {
        this.thamSo.setDefaults();
        return {
            success: true,
            message: 'Khôi phục tham số mặc định thành công'
        };
    }
}
