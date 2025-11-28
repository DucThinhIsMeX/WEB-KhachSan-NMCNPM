class Phong {
    constructor(maPhong, tenPhong, loaiPhong, donGia, ghiChu = '') {
        this.maPhong = maPhong;
        this.tenPhong = tenPhong;
        this.loaiPhong = loaiPhong; // A, B, C
        this.donGia = donGia;
        this.ghiChu = ghiChu;
        this.trangThai = 'TRONG'; // TRONG, DANG_THUE
    }

    static fromJSON(json) {
        const phong = new Phong(
            json.maPhong,
            json.tenPhong,
            json.loaiPhong,
            json.donGia,
            json.ghiChu
        );
        phong.trangThai = json.trangThai;
        return phong;
    }

    toJSON() {
        return {
            maPhong: this.maPhong,
            tenPhong: this.tenPhong,
            loaiPhong: this.loaiPhong,
            donGia: this.donGia,
            ghiChu: this.ghiChu,
            trangThai: this.trangThai
        };
    }

    setTrangThai(trangThai) {
        this.trangThai = trangThai;
    }

    isTrong() {
        return this.trangThai === 'TRONG';
    }
}
