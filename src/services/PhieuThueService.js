class PhieuThueService {
    constructor(phongService, thamSo) {
        this.storageKey = 'danhsach_phieuthue';
        this.phongService = phongService;
        this.thamSo = thamSo;
        this.loadData();
    }

    loadData() {
        const data = localStorage.getItem(this.storageKey);
        this.danhSachPhieu = data ? JSON.parse(data).map(p => PhieuThue.fromJSON(p)) : [];
    }

    saveData() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.danhSachPhieu.map(p => p.toJSON())));
    }

    generateMaPhieu() {
        const count = this.danhSachPhieu.length + 1;
        return `PT${String(count).padStart(5, '0')}`;
    }

    // YC2: Lập phiếu thuê phòng (BM2)
    // D1: Phòng, Ngày bắt đầu thuê, Tên KH, Loại Khách, CMND, Địa Chỉ
    // D3: Danh sách loại khách, số lượng khách tối đa = 3 (QĐ2)
    lapPhieuThue(data) {
        // Bước 1: Nhận D1 từ người dùng
        const { maPhong, ngayBatDau, danhSachKhach } = data;

        // Bước 2: Đọc D3 từ bộ nhớ phụ (Tham số QĐ2)
        const soKhachToiDa = this.thamSo.soKhachToiDa;
        const loaiKhachHopLe = this.thamSo.loaiKhach;

        // Bước 3: Kiểm tra QĐ2
        // Kiểm tra số lượng khách <= 3
        if (danhSachKhach.length > soKhachToiDa) {
            throw new Error(`Số lượng khách vượt quá quy định (tối đa ${soKhachToiDa} khách)`);
        }

        if (danhSachKhach.length === 0) {
            throw new Error('Phải có ít nhất 1 khách');
        }

        // Kiểm tra loại khách hợp lệ
        for (const khach of danhSachKhach) {
            if (!loaiKhachHopLe.includes(khach.loaiKhach)) {
                throw new Error('Loại khách không hợp lệ');
            }
        }

        // Kiểm tra phòng có tồn tại và còn trống
        const phong = this.phongService.layPhong(maPhong);
        if (!phong) {
            throw new Error('Không tìm thấy phòng');
        }
        if (!phong.isTrong()) {
            throw new Error('Phòng đang được thuê');
        }

        // Bước 4: Lưu D4 (phiếu thuê) xuống bộ nhớ phụ
        const maPhieuThue = this.generateMaPhieu();
        const khachHangList = danhSachKhach.map(k => new KhachHang(
            k.tenKhach,
            k.loaiKhach,
            k.cmnd,
            k.diaChi
        ));

        const phieuThue = new PhieuThue(maPhieuThue, maPhong, ngayBatDau, khachHangList);
        this.danhSachPhieu.push(phieuThue);
        this.saveData();

        // Cập nhật trạng thái phòng
        this.phongService.capNhatTrangThaiPhong(maPhong, 'DANG_THUE');

        // Bước 5: Trả D6 (thông báo thành công)
        return {
            success: true,
            message: 'Lập phiếu thuê phòng thành công',
            data: phieuThue
        };
    }

    layDanhSachPhieu(filter = {}) {
        let result = [...this.danhSachPhieu];

        if (filter.maPhong) {
            result = result.filter(p => p.maPhong === filter.maPhong);
        }

        if (filter.trangThai) {
            result = result.filter(p => p.trangThai === filter.trangThai);
        }

        return result;
    }

    layPhieuThue(maPhieuThue) {
        return this.danhSachPhieu.find(p => p.maPhieuThue === maPhieuThue);
    }

    layPhieuDangThue() {
        return this.danhSachPhieu.filter(p => p.trangThai === 'DANG_THUE');
    }

    traPhong(maPhieuThue, ngayKetThuc) {
        const phieu = this.layPhieuThue(maPhieuThue);
        if (!phieu) {
            throw new Error('Không tìm thấy phiếu thuê');
        }
        if (phieu.trangThai === 'DA_TRA') {
            throw new Error('Phòng đã được trả');
        }

        phieu.traPhong(ngayKetThuc);
        this.saveData();

        // Cập nhật trạng thái phòng
        this.phongService.capNhatTrangThaiPhong(phieu.maPhong, 'TRONG');

        return phieu;
    }
}
