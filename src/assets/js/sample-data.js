// Tạo dữ liệu mẫu cho hệ thống
function initSampleData() {
    console.log('🔄 Đang khởi tạo dữ liệu mẫu...');

    // Xóa dữ liệu cũ
    localStorage.removeItem('danhsach_phong');
    localStorage.removeItem('danhsach_phieuthue');
    localStorage.removeItem('danhsach_hoadon');

    // 1. Thêm phòng mẫu
    const danhSachPhongMau = [
        { maPhong: 'P101', tenPhong: 'Phòng 101', loaiPhong: 'A', donGia: 150000, ghiChu: 'Tầng 1', trangThai: 'TRONG' },
        { maPhong: 'P102', tenPhong: 'Phòng 102', loaiPhong: 'A', donGia: 150000, ghiChu: 'Tầng 1', trangThai: 'TRONG' },
        { maPhong: 'P103', tenPhong: 'Phòng 103', loaiPhong: 'B', donGia: 170000, ghiChu: 'Tầng 1', trangThai: 'TRONG' },
        { maPhong: 'P201', tenPhong: 'Phòng 201', loaiPhong: 'B', donGia: 170000, ghiChu: 'Tầng 2', trangThai: 'TRONG' },
        { maPhong: 'P202', tenPhong: 'Phòng 202', loaiPhong: 'C', donGia: 200000, ghiChu: 'Tầng 2 - VIP', trangThai: 'TRONG' },
        { maPhong: 'P203', tenPhong: 'Phòng 203', loaiPhong: 'C', donGia: 200000, ghiChu: 'Tầng 2 - VIP', trangThai: 'TRONG' },
    ];

    localStorage.setItem('danhsach_phong', JSON.stringify(danhSachPhongMau));

    console.log('✅ Đã tạo', danhSachPhongMau.length, 'phòng mẫu');
    console.log('✅ Dữ liệu mẫu đã được khởi tạo thành công!');
    
    showNotification('Đã tạo dữ liệu mẫu thành công!', 'success');
}

function clearAllData() {
    if (confirm('⚠️ Bạn có chắc chắn muốn xóa TẤT CẢ dữ liệu?\n\nHành động này không thể hoàn tác!')) {
        localStorage.clear();
        location.reload();
    }
}
