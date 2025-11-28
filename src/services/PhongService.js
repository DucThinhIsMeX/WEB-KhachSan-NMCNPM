class PhongService {
    constructor() {
        this.storageKey = 'danhsach_phong';
        this.loadData();
    }

    loadData() {
        const data = localStorage.getItem(this.storageKey);
        this.danhSachPhong = data ? JSON.parse(data).map(p => Phong.fromJSON(p)) : [];
    }

    saveData() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.danhSachPhong.map(p => p.toJSON())));
    }

    // YC1: Lập danh mục phòng (BM1)
    themPhong(phong) {
        // Kiểm tra trùng mã phòng
        if (this.danhSachPhong.find(p => p.maPhong === phong.maPhong)) {
            throw new Error('Mã phòng đã tồn tại');
        }
        this.danhSachPhong.push(phong);
        this.saveData();
        return true;
    }

    capNhatPhong(maPhong, dataUpdate) {
        const phong = this.danhSachPhong.find(p => p.maPhong === maPhong);
        if (!phong) {
            throw new Error('Không tìm thấy phòng');
        }
        Object.assign(phong, dataUpdate);
        this.saveData();
        return phong;
    }

    xoaPhong(maPhong) {
        const index = this.danhSachPhong.findIndex(p => p.maPhong === maPhong);
        if (index === -1) {
            throw new Error('Không tìm thấy phòng');
        }
        // Kiểm tra phòng có đang được thuê không
        if (this.danhSachPhong[index].trangThai === 'DANG_THUE') {
            throw new Error('Không thể xóa phòng đang được thuê');
        }
        this.danhSachPhong.splice(index, 1);
        this.saveData();
        return true;
    }

    layDanhSachPhong(filter = {}) {
        let result = [...this.danhSachPhong];
        
        if (filter.loaiPhong) {
            result = result.filter(p => p.loaiPhong === filter.loaiPhong);
        }
        
        if (filter.trangThai) {
            result = result.filter(p => p.trangThai === filter.trangThai);
        }
        
        return result;
    }

    layPhong(maPhong) {
        return this.danhSachPhong.find(p => p.maPhong === maPhong);
    }

    capNhatTrangThaiPhong(maPhong, trangThai) {
        const phong = this.layPhong(maPhong);
        if (phong) {
            phong.setTrangThai(trangThai);
            this.saveData();
        }
    }

    // YC3: Tra cứu phòng (BM3)
    traCuuPhong(criteria) {
        let result = [...this.danhSachPhong];

        if (criteria.loaiPhong) {
            result = result.filter(p => p.loaiPhong === criteria.loaiPhong);
        }

        if (criteria.trangThai) {
            result = result.filter(p => p.trangThai === criteria.trangThai);
        }

        return result;
    }

    layPhongTrong() {
        return this.danhSachPhong.filter(p => p.trangThai === 'TRONG');
    }

    getThongKe() {
        return {
            tongSoPhong: this.danhSachPhong.length,
            phongTrong: this.danhSachPhong.filter(p => p.trangThai === 'TRONG').length,
            phongDangThue: this.danhSachPhong.filter(p => p.trangThai === 'DANG_THUE').length
        };
    }
}
