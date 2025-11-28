class PhieuThue {
    constructor(maPhieuThue, maPhong, ngayBatDau, danhSachKhach) {
        this.maPhieuThue = maPhieuThue;
        this.maPhong = maPhong;
        this.ngayBatDau = ngayBatDau;
        this.danhSachKhach = danhSachKhach; // Array of KhachHang
        this.ngayKetThuc = null;
        this.trangThai = 'DANG_THUE'; // DANG_THUE, DA_TRA
    }

    static fromJSON(json) {
        const phieu = new PhieuThue(
            json.maPhieuThue,
            json.maPhong,
            json.ngayBatDau,
            json.danhSachKhach.map(k => KhachHang.fromJSON(k))
        );
        phieu.ngayKetThuc = json.ngayKetThuc;
        phieu.trangThai = json.trangThai;
        return phieu;
    }

    toJSON() {
        return {
            maPhieuThue: this.maPhieuThue,
            maPhong: this.maPhong,
            ngayBatDau: this.ngayBatDau,
            ngayKetThuc: this.ngayKetThuc,
            danhSachKhach: this.danhSachKhach.map(k => k.toJSON()),
            trangThai: this.trangThai
        };
    }

    getSoKhach() {
        return this.danhSachKhach.length;
    }

    hasKhachNuocNgoai() {
        return this.danhSachKhach.some(k => k.isNuocNgoai());
    }

    getSoNgayThue(ngayKetThuc) {
        const start = new Date(this.ngayBatDau);
        const end = new Date(ngayKetThuc || new Date());
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays || 1;
    }

    traPhong(ngayKetThuc) {
        this.ngayKetThuc = ngayKetThuc;
        this.trangThai = 'DA_TRA';
    }
}
