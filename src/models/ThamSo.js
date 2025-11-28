class ThamSo {
    constructor() {
        this.loadFromLocalStorage();
    }

    loadFromLocalStorage() {
        const saved = localStorage.getItem('thamso');
        if (saved) {
            const data = JSON.parse(saved);
            this.donGiaPhong = data.donGiaPhong;
            this.loaiKhach = data.loaiKhach;
            this.soKhachToiDa = data.soKhachToiDa;
            this.phuThuKhach3 = data.phuThuKhach3;
            this.heSoNuocNgoai = data.heSoNuocNgoai;
        } else {
            this.setDefaults();
        }
    }

    setDefaults() {
        // QĐ1: 3 loại phòng với đơn giá
        this.donGiaPhong = {
            'A': 150000,
            'B': 170000,
            'C': 200000
        };

        // QĐ2: 2 loại khách
        this.loaiKhach = ['NOI_DIA', 'NUOC_NGOAI'];
        this.soKhachToiDa = 3;

        // QĐ4: Tỷ lệ phụ thu và hệ số
        this.phuThuKhach3 = 0.25; // 25%
        this.heSoNuocNgoai = 1.5;

        this.save();
    }

    save() {
        const data = {
            donGiaPhong: this.donGiaPhong,
            loaiKhach: this.loaiKhach,
            soKhachToiDa: this.soKhachToiDa,
            phuThuKhach3: this.phuThuKhach3,
            heSoNuocNgoai: this.heSoNuocNgoai,
            lastUpdate: new Date().toISOString()
        };
        localStorage.setItem('thamso', JSON.stringify(data));
    }

    getDonGiaPhong(loaiPhong) {
        return this.donGiaPhong[loaiPhong] || 0;
    }

    updateDonGiaPhong(loaiPhong, donGia) {
        this.donGiaPhong[loaiPhong] = donGia;
        this.save();
    }

    updateThamSo(data) {
        if (data.donGiaA) this.donGiaPhong.A = parseInt(data.donGiaA);
        if (data.donGiaB) this.donGiaPhong.B = parseInt(data.donGiaB);
        if (data.donGiaC) this.donGiaPhong.C = parseInt(data.donGiaC);
        if (data.soKhachToiDa) this.soKhachToiDa = parseInt(data.soKhachToiDa);
        if (data.phuThuKhach3) this.phuThuKhach3 = parseFloat(data.phuThuKhach3) / 100;
        if (data.heSoNuocNgoai) this.heSoNuocNgoai = parseFloat(data.heSoNuocNgoai);
        this.save();
    }

    getAll() {
        return {
            donGiaPhong: this.donGiaPhong,
            loaiKhach: this.loaiKhach,
            soKhachToiDa: this.soKhachToiDa,
            phuThuKhach3: this.phuThuKhach3,
            heSoNuocNgoai: this.heSoNuocNgoai
        };
    }
}
