class HoaDonService {
    constructor(phieuThueService, phongService, thamSo) {
        this.storageKey = 'danhsach_hoadon';
        this.phieuThueService = phieuThueService;
        this.phongService = phongService;
        this.thamSo = thamSo;
        this.loadData();
    }

    loadData() {
        const data = localStorage.getItem(this.storageKey);
        this.danhSachHoaDon = data ? JSON.parse(data).map(h => HoaDon.fromJSON(h)) : [];
    }

    saveData() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.danhSachHoaDon.map(h => h.toJSON())));
    }

    generateMaHoaDon() {
        const count = this.danhSachHoaDon.length + 1;
        return `HD${String(count).padStart(5, '0')}`;
    }

    // YC4: Lập hóa đơn thanh toán (BM4)
    // D1: Khách hàng/Cơ quan, Địa chỉ
    // D3: Thông tin phiếu thuê, Chi tiết khách, Đơn giá cơ bản
    lapHoaDon(data) {
        // Bước 1: Nhận D1 và tra cứu D3
        const { maPhieuThue, khachHang, diaChi, ngayKetThuc } = data;

        const phieuThue = this.phieuThueService.layPhieuThue(maPhieuThue);
        if (!phieuThue) {
            throw new Error('Không tìm thấy phiếu thuê');
        }

        if (phieuThue.trangThai === 'DA_TRA') {
            throw new Error('Phiếu thuê đã được thanh toán');
        }

        const phong = this.phongService.layPhong(phieuThue.maPhong);
        if (!phong) {
            throw new Error('Không tìm thấy thông tin phòng');
        }

        // Bước 2: Tính toán Đơn Giá Điều Chỉnh theo QĐ4
        const soNgayThue = phieuThue.getSoNgayThue(ngayKetThuc);
        const donGiaCoBan = phong.donGia;
        const soKhach = phieuThue.getSoKhach();
        const coKhachNuocNgoai = phieuThue.hasKhachNuocNgoai();

        // Đọc tham số từ D3
        const phuThuKhach3 = this.thamSo.phuThuKhach3;
        const heSoNuocNgoai = this.thamSo.heSoNuocNgoai;

        let donGiaDieuChinh = donGiaCoBan;

        // Áp dụng phụ thu khách thứ 3: Nếu có >= 3 khách, phụ thu 25%
        if (soKhach >= 3) {
            donGiaDieuChinh = donGiaCoBan * (1 + phuThuKhach3);
        }

        // Áp dụng hệ số khách nước ngoài: Nếu có ít nhất 1 khách nước ngoài, nhân 1.5
        if (coKhachNuocNgoai) {
            donGiaDieuChinh = donGiaDieuChinh * heSoNuocNgoai;
        }

        // Bước 3: Tính Thành Tiền = Đơn Giá Điều Chỉnh × Số Ngày Thuê
        const thanhTien = donGiaDieuChinh * soNgayThue;

        // Tạo hóa đơn
        const maHoaDon = this.generateMaHoaDon();
        const hoaDon = new HoaDon(maHoaDon, maPhieuThue, khachHang, diaChi);

        const chiTiet = {
            maPhong: phong.maPhong,
            tenPhong: phong.tenPhong,
            loaiPhong: phong.loaiPhong,
            soNgayThue: soNgayThue,
            donGiaCoBan: donGiaCoBan,
            soKhach: soKhach,
            coKhachNuocNgoai: coKhachNuocNgoai,
            phuThu: soKhach >= 3 ? phuThuKhach3 : 0,
            heSo: coKhachNuocNgoai ? heSoNuocNgoai : 1,
            donGiaDieuChinh: donGiaDieuChinh,
            thanhTien: thanhTien
        };

        hoaDon.themChiTiet(chiTiet);

        // Bước 5: Lưu D4 (hóa đơn) xuống bộ nhớ phụ
        this.danhSachHoaDon.push(hoaDon);
        this.saveData();

        // Trả phòng
        this.phieuThueService.traPhong(maPhieuThue, ngayKetThuc || new Date().toISOString().split('T')[0]);

        // Bước 6: Trả D6 (BM4 và thông báo)
        return {
            success: true,
            message: 'Lập hóa đơn thành công',
            data: hoaDon
        };
    }

    layDanhSachHoaDon(filter = {}) {
        let result = [...this.danhSachHoaDon];

        if (filter.thang && filter.nam) {
            result = result.filter(h => {
                const date = new Date(h.ngayLap);
                return date.getMonth() + 1 === filter.thang && 
                       date.getFullYear() === filter.nam;
            });
        }

        return result;
    }

    layHoaDon(maHoaDon) {
        return this.danhSachHoaDon.find(h => h.maHoaDon === maHoaDon);
    }

    tinhChiTietThanhToan(maPhieuThue) {
        const phieuThue = this.phieuThueService.layPhieuThue(maPhieuThue);
        if (!phieuThue) {
            return null;
        }

        const phong = this.phongService.layPhong(phieuThue.maPhong);
        const soNgayThue = phieuThue.getSoNgayThue();
        const donGiaCoBan = phong.donGia;
        const soKhach = phieuThue.getSoKhach();
        const coKhachNuocNgoai = phieuThue.hasKhachNuocNgoai();

        const phuThuKhach3 = this.thamSo.phuThuKhach3;
        const heSoNuocNgoai = this.thamSo.heSoNuocNgoai;

        let donGiaDieuChinh = donGiaCoBan;
        let apDungPhuThu = false;
        let apDungHeSo = 1;

        if (soKhach >= 3) {
            donGiaDieuChinh = donGiaCoBan * (1 + phuThuKhach3);
            apDungPhuThu = true;
        }

        if (coKhachNuocNgoai) {
            donGiaDieuChinh = donGiaDieuChinh * heSoNuocNgoai;
            apDungHeSo = heSoNuocNgoai;
        }

        const thanhTien = donGiaDieuChinh * soNgayThue;

        return {
            phong: phong,
            phieuThue: phieuThue,
            soNgayThue: soNgayThue,
            donGiaCoBan: donGiaCoBan,
            soKhach: soKhach,
            coKhachNuocNgoai: coKhachNuocNgoai,
            apDungPhuThu: apDungPhuThu,
            phuThu: phuThuKhach3,
            apDungHeSo: apDungHeSo,
            donGiaDieuChinh: donGiaDieuChinh,
            thanhTien: thanhTien
        };
    }
}
