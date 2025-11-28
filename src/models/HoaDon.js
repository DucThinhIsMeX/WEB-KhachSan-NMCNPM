class HoaDon {
    constructor(maHoaDon, maPhieuThue, khachHang, diaChi) {
        this.maHoaDon = maHoaDon;
        this.maPhieuThue = maPhieuThue;
        this.khachHang = khachHang;
        this.diaChi = diaChi;
        this.ngayLap = new Date().toISOString();
        this.chiTiet = []; // Array of {maPhong, loaiPhong, soNgay, donGiaCoBan, donGiaDieuChinh, thanhTien}
        this.triGia = 0;
    }

    static fromJSON(json) {
        const hd = new HoaDon(
            json.maHoaDon,
            json.maPhieuThue,
            json.khachHang,
            json.diaChi
        );
        hd.ngayLap = json.ngayLap;
        hd.chiTiet = json.chiTiet;
        hd.triGia = json.triGia;
        return hd;
    }

    toJSON() {
        return {
            maHoaDon: this.maHoaDon,
            maPhieuThue: this.maPhieuThue,
            khachHang: this.khachHang,
            diaChi: this.diaChi,
            ngayLap: this.ngayLap,
            chiTiet: this.chiTiet,
            triGia: this.triGia
        };
    }

    themChiTiet(chiTiet) {
        this.chiTiet.push(chiTiet);
        this.tinhTriGia();
    }

    tinhTriGia() {
        this.triGia = this.chiTiet.reduce((sum, ct) => sum + ct.thanhTien, 0);
    }

    getThang() {
        return new Date(this.ngayLap).getMonth() + 1;
    }

    getNam() {
        return new Date(this.ngayLap).getFullYear();
    }
}
