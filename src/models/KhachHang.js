class KhachHang {
    constructor(tenKhach, loaiKhach, cmnd, diaChi) {
        this.tenKhach = tenKhach;
        this.loaiKhach = loaiKhach; // NOI_DIA, NUOC_NGOAI
        this.cmnd = cmnd;
        this.diaChi = diaChi;
    }

    static fromJSON(json) {
        return new KhachHang(
            json.tenKhach,
            json.loaiKhach,
            json.cmnd,
            json.diaChi
        );
    }

    toJSON() {
        return {
            tenKhach: this.tenKhach,
            loaiKhach: this.loaiKhach,
            cmnd: this.cmnd,
            diaChi: this.diaChi
        };
    }

    isNuocNgoai() {
        return this.loaiKhach === 'NUOC_NGOAI';
    }
}
