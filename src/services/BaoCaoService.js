class BaoCaoService {
    constructor(hoaDonService) {
        this.hoaDonService = hoaDonService;
    }

    // YC5: Lập báo cáo tháng (BM5)
    // D1: Tháng
    // D3: Dữ liệu hóa đơn
    // D4: Doanh thu, Tỷ lệ theo loại phòng
    lapBaoCaoThang(thang, nam) {
        // Bước 1: Nhận D1 (Tháng) từ người dùng
        if (!thang || !nam) {
            throw new Error('Vui lòng chọn tháng và năm');
        }

        // Bước 2: Đọc D3 (dữ liệu hóa đơn) từ bộ nhớ phụ
        const danhSachHoaDon = this.hoaDonService.layDanhSachHoaDon({ thang, nam });

        // Bước 3: Thống kê và tính Doanh Thu theo từng Loại Phòng
        const doanhThuTheoLoai = {
            'A': 0,
            'B': 0,
            'C': 0
        };

        danhSachHoaDon.forEach(hoaDon => {
            hoaDon.chiTiet.forEach(ct => {
                if (doanhThuTheoLoai.hasOwnProperty(ct.loaiPhong)) {
                    doanhThuTheoLoai[ct.loaiPhong] += ct.thanhTien;
                }
            });
        });

        // Bước 4: Tính Tổng Doanh Thu
        const tongDoanhThu = Object.values(doanhThuTheoLoai).reduce((sum, dt) => sum + dt, 0);

        // Bước 5: Tính Tỷ Lệ Doanh Thu
        const tyLeDoanhThu = {};
        Object.keys(doanhThuTheoLoai).forEach(loai => {
            tyLeDoanhThu[loai] = tongDoanhThu > 0 
                ? (doanhThuTheoLoai[loai] / tongDoanhThu * 100).toFixed(2)
                : 0;
        });

        const baoCao = {
            thang: thang,
            nam: nam,
            doanhThu: doanhThuTheoLoai,
            tyLe: tyLeDoanhThu,
            tongDoanhThu: tongDoanhThu,
            soHoaDon: danhSachHoaDon.length
        };

        // Bước 6: Lưu D4 và trả D6 (BM5)
        return {
            success: true,
            message: 'Lập báo cáo thành công',
            data: baoCao
        };
    }

    layBaoCaoTheoNam(nam) {
        const baoCaoTheoThang = [];
        
        for (let thang = 1; thang <= 12; thang++) {
            const baoCao = this.lapBaoCaoThang(thang, nam);
            baoCaoTheoThang.push(baoCao.data);
        }

        return baoCaoTheoThang;
    }

    thongKeDoanhThu(tuNgay, denNgay) {
        const allHoaDon = this.hoaDonService.layDanhSachHoaDon({});
        
        const filtered = allHoaDon.filter(hd => {
            const ngayLap = new Date(hd.ngayLap);
            return ngayLap >= new Date(tuNgay) && ngayLap <= new Date(denNgay);
        });

        const tongDoanhThu = filtered.reduce((sum, hd) => sum + hd.triGia, 0);

        return {
            soHoaDon: filtered.length,
            tongDoanhThu: tongDoanhThu,
            danhSach: filtered
        };
    }
}
