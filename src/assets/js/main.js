// ========== SPA ROUTER ==========
const APP_ROUTES = {
    'dashboard': renderDashboard,
    'phong/danh-sach': renderDanhSachPhong,
    'phong/them-phong': renderThemPhong,
    'phieu-thue/danh-sach': renderDanhSachPhieu,
    'phieu-thue/them-phieu': renderThemPhieu,
    'phieu-thue/tra-cuu': renderTraCuuPhong,
    'hoa-don/danh-sach': renderDanhSachHoaDon,
    'hoa-don/tao-hoa-don': renderTaoHoaDon,
    'bao-cao/bao-cao-thang': renderBaoCaoThang,
    'tham-so/quan-ly-tham-so': renderQuanLyThamSo,
    'dev-tools': renderDevTools
};

let currentPage = 'dashboard';

// ========== KHỞI TẠO HỆ THỐNG ==========
let thamSo;
let phongService;
let phieuThueService;
let hoaDonService;
let baoCaoService;

let phongController;
let phieuThueController;
let hoaDonController;
let baoCaoController;
let thamSoController;

document.addEventListener('DOMContentLoaded', function() {
    initializeSystem();
    loadPage('dashboard');
    updateActiveMenu('dashboard');
});

function initializeSystem() {
    thamSo = new ThamSo();
    phongService = new PhongService();
    phieuThueService = new PhieuThueService(phongService, thamSo);
    hoaDonService = new HoaDonService(phieuThueService, phongService, thamSo);
    baoCaoService = new BaoCaoService(hoaDonService);

    phongController = new PhongController(phongService, thamSo);
    phieuThueController = new PhieuThueController(phieuThueService, phongService, thamSo);
    hoaDonController = new HoaDonController(hoaDonService, phieuThueService);
    baoCaoController = new BaoCaoController(baoCaoService);
    thamSoController = new ThamSoController(thamSo);

    console.log('✅ Hệ thống SPA đã được khởi tạo');
}

// ========== SPA NAVIGATION ==========
function loadPage(page) {
    currentPage = page;
    
    const renderFunction = APP_ROUTES[page];
    if (renderFunction) {
        renderFunction();
        updateActiveMenu(page);
    } else {
        console.error('Page not found:', page);
        renderDashboard();
    }
}

function updateActiveMenu(page) {
    document.querySelectorAll('.menu-item a').forEach(link => {
        link.parentElement.classList.remove('active');
    });
    
    const activeLink = document.querySelector(`a[onclick*="${page}"]`);
    if (activeLink) {
        activeLink.parentElement.classList.add('active');
    }
}

// ========== DASHBOARD ==========
function renderDashboard() {
    const stats = phongService.getThongKe();
    const now = new Date();
    const baoCao = baoCaoController.lapBaoCaoThang(now.getMonth() + 1, now.getFullYear());

    const content = document.getElementById('content');
    content.innerHTML = `
        <h1>Chào mừng đến với Hệ thống Quản lý Khách sạn</h1>
        <div class="dashboard">
            <div class="card">
                <h3>Tổng số phòng</h3>
                <p class="stat-number">${stats.tongSoPhong}</p>
            </div>
            <div class="card">
                <h3>Phòng đang thuê</h3>
                <p class="stat-number">${stats.phongDangThue}</p>
            </div>
            <div class="card">
                <h3>Doanh thu tháng</h3>
                <p class="stat-number">${baoCao.success ? formatCurrency(baoCao.data.tongDoanhThu) : '0 VNĐ'}</p>
            </div>
        </div>
    `;
}

// ========== YC1: QUẢN LÝ PHÒNG ==========
function renderDanhSachPhong() {
    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Danh Mục Phòng (BM1)</h2>
            <button class="btn btn-primary" onclick="loadPage('phong/them-phong')">+ Thêm Phòng</button>
        </div>

        <div class="filter-section">
            <select id="filterLoaiPhong" class="form-control" onchange="loadDanhSachPhong()">
                <option value="">Tất cả loại phòng</option>
                <option value="A">Loại A</option>
                <option value="B">Loại B</option>
                <option value="C">Loại C</option>
            </select>
            <select id="filterTrangThai" class="form-control" onchange="loadDanhSachPhong()">
                <option value="">Tất cả trạng thái</option>
                <option value="TRONG">Phòng trống</option>
                <option value="DANG_THUE">Đang thuê</option>
            </select>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Phòng</th>
                    <th>Tên Phòng</th>
                    <th>Loại Phòng</th>
                    <th>Đơn Giá</th>
                    <th>Trạng Thái</th>
                    <th>Ghi Chú</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody id="tbodyPhong"></tbody>
        </table>
    `;

    loadDanhSachPhong();
}

function loadDanhSachPhong() {
    const loaiPhong = document.getElementById('filterLoaiPhong')?.value || '';
    const trangThai = document.getElementById('filterTrangThai')?.value || '';

    const danhSach = phongController.locPhong(loaiPhong, trangThai);
    const tbody = document.getElementById('tbodyPhong');
    
    tbody.innerHTML = '';
    danhSach.forEach((phong) => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${phong.maPhong}</td>
            <td>${phong.tenPhong}</td>
            <td>${phong.loaiPhong}</td>
            <td>${formatCurrency(phong.donGia)}</td>
            <td><span class="badge ${phong.trangThai === 'TRONG' ? 'badge-success' : 'badge-warning'}">
                ${phong.trangThai === 'TRONG' ? 'Phòng trống' : 'Đang thuê'}
            </span></td>
            <td>${phong.ghiChu}</td>
            <td>
                <button class="btn-action btn-delete" onclick="deletePhong('${phong.maPhong}')">Xóa</button>
            </td>
        `;
    });
}

function renderThemPhong() {
    const data = phongController.hienThiFormThemPhong();
    
    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Thêm Phòng Mới</h2>
        </div>

        <form id="formThemPhong" class="form-container">
            <div class="form-group">
                <label for="maPhong">Mã Phòng <span class="required">*</span></label>
                <input type="text" id="maPhong" name="maPhong" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="tenPhong">Tên Phòng <span class="required">*</span></label>
                <input type="text" id="tenPhong" name="tenPhong" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="loaiPhong">Loại Phòng <span class="required">*</span></label>
                <select id="loaiPhong" name="loaiPhong" class="form-control" required>
                    <option value="">-- Chọn loại phòng --</option>
                    <option value="A">Loại A (${formatCurrency(data.donGiaPhong.A)})</option>
                    <option value="B">Loại B (${formatCurrency(data.donGiaPhong.B)})</option>
                    <option value="C">Loại C (${formatCurrency(data.donGiaPhong.C)})</option>
                </select>
            </div>

            <div class="form-group">
                <label for="donGia">Đơn Giá <span class="required">*</span></label>
                <input type="number" id="donGia" name="donGia" class="form-control" readonly>
            </div>

            <div class="form-group">
                <label for="ghiChu">Ghi Chú</label>
                <textarea id="ghiChu" name="ghiChu" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <button type="button" class="btn btn-secondary" onclick="loadPage('phong/danh-sach')">Hủy</button>
            </div>
        </form>
    `;

    // Gắn sự kiện
    document.getElementById('loaiPhong').addEventListener('change', function() {
        const loai = this.value;
        const donGia = data.donGiaPhong[loai] || 0;
        document.getElementById('donGia').value = donGia;
    });

    document.getElementById('formThemPhong').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            maPhong: document.getElementById('maPhong').value,
            tenPhong: document.getElementById('tenPhong').value,
            loaiPhong: document.getElementById('loaiPhong').value,
            donGia: document.getElementById('donGia').value,
            ghiChu: document.getElementById('ghiChu').value
        };

        const result = phongController.themPhong(formData);
        
        if (result.success) {
            showNotification(result.message, 'success');
            loadPage('phong/danh-sach');
        } else {
            showNotification(result.message, 'error');
        }
    });
}

function deletePhong(maPhong) {
    if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
        const result = phongController.xoaPhong(maPhong);
        if (result.success) {
            showNotification(result.message, 'success');
            loadDanhSachPhong();
        } else {
            showNotification(result.message, 'error');
        }
    }
}

// ========== YC2: LẬP PHIẾU THUÊ ==========
function renderDanhSachPhieu() {
    const danhSach = phieuThueController.hienThiDanhSachPhieu();
    
    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Danh Sách Phiếu Thuê (BM2)</h2>
            <button class="btn btn-primary" onclick="loadPage('phieu-thue/them-phieu')">+ Thêm Phiếu Thuê</button>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Phiếu</th>
                    <th>Phòng</th>
                    <th>Ngày Thuê</th>
                    <th>Số Khách</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody id="tbodyPhieu"></tbody>
        </table>
    `;

    const tbody = document.getElementById('tbodyPhieu');
    danhSach.forEach(phieu => {
        const phong = phongService.layPhong(phieu.maPhong);
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${phieu.maPhieuThue}</td>
            <td>${phong?.tenPhong || phieu.maPhong}</td>
            <td>${formatDate(phieu.ngayBatDau)}</td>
            <td>${phieu.getSoKhach()} khách</td>
            <td><span class="badge ${phieu.trangThai === 'DANG_THUE' ? 'badge-warning' : 'badge-success'}">
                ${phieu.trangThai === 'DANG_THUE' ? 'Đang thuê' : 'Đã trả'}
            </span></td>
            <td>
                <button class="btn-action btn-edit" onclick="alert('Chi tiết phiếu: ${phieu.maPhieuThue}')">Chi tiết</button>
            </td>
        `;
    });
}

function renderThemPhieu() {
    const data = phieuThueController.hienThiFormThemPhieu();
    let soKhach = 1;

    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Lập Phiếu Thuê Phòng (BM2)</h2>
        </div>

        <form id="formPhieuThue" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="phong">Phòng <span class="required">*</span></label>
                    <select id="phong" name="phong" class="form-control" required>
                        <option value="">-- Chọn phòng --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ngayBatDau">Ngày Bắt Đầu Thuê <span class="required">*</span></label>
                    <input type="date" id="ngayBatDau" name="ngayBatDau" class="form-control" required>
                </div>
            </div>

            <h3 class="section-title">Thông Tin Khách Hàng</h3>
            
            <div id="danhSachKhach"></div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="btnThemKhach">+ Thêm Khách (Tối đa ${data.soKhachToiDa})</button>
            </div>

            <div class="alert alert-info">
                <strong>Quy định (QĐ2):</strong> Mỗi phòng tối đa ${data.soKhachToiDa} khách. Hỗ trợ 2 loại khách: Nội địa và Nước ngoài.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu Phiếu Thuê</button>
                <button type="button" class="btn btn-secondary" onclick="loadPage('phieu-thue/danh-sach')">Hủy</button>
            </div>
        </form>
    `;

    // Load phòng trống
    const selectPhong = document.getElementById('phong');
    data.danhSachPhong.forEach(phong => {
        const option = document.createElement('option');
        option.value = phong.maPhong;
        option.textContent = `${phong.tenPhong} - ${phong.loaiPhong} (${formatCurrency(phong.donGia)})`;
        selectPhong.appendChild(option);
    });

    // Set ngày mặc định
    document.getElementById('ngayBatDau').valueAsDate = new Date();

    // Thêm khách đầu tiên
    themKhachMoi(1);

    // Thêm khách
    document.getElementById('btnThemKhach').addEventListener('click', function() {
        if (soKhach >= data.soKhachToiDa) {
            showNotification(`Tối đa ${data.soKhachToiDa} khách (QĐ2)`, 'warning');
            return;
        }
        soKhach++;
        themKhachMoi(soKhach);
    });

    // Submit
    document.getElementById('formPhieuThue').addEventListener('submit', submitPhieuThue);
}

function themKhachMoi(soThuTu) {
    const container = document.getElementById('danhSachKhach');
    const khachItem = document.createElement('div');
    khachItem.className = 'khach-item';
    khachItem.innerHTML = `
        <h4>Khách ${soThuTu} ${soThuTu > 1 ? '<button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">×</button>' : '<span class="required">*</span>'}</h4>
        <div class="form-row">
            <div class="form-group">
                <label>Tên Khách Hàng</label>
                <input type="text" name="tenKhach[]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Loại Khách</label>
                <select name="loaiKhach[]" class="form-control" required>
                    <option value="NOI_DIA">Nội địa</option>
                    <option value="NUOC_NGOAI">Nước ngoài</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>CMND/CCCD</label>
                <input type="text" name="cmnd[]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Địa Chỉ</label>
                <input type="text" name="diaChi[]" class="form-control" required>
            </div>
        </div>
    `;
    container.appendChild(khachItem);
}

function submitPhieuThue(e) {
    e.preventDefault();

    const maPhong = document.getElementById('phong').value;
    const ngayBatDau = document.getElementById('ngayBatDau').value;

    const tenKhachList = document.getElementsByName('tenKhach[]');
    const loaiKhachList = document.getElementsByName('loaiKhach[]');
    const cmndList = document.getElementsByName('cmnd[]');
    const diaChiList = document.getElementsByName('diaChi[]');

    const danhSachKhach = [];
    for (let i = 0; i < tenKhachList.length; i++) {
        danhSachKhach.push({
            tenKhach: tenKhachList[i].value,
            loaiKhach: loaiKhachList[i].value,
            cmnd: cmndList[i].value,
            diaChi: diaChiList[i].value
        });
    }

    const result = phieuThueController.lapPhieuThue({
        maPhong,
        ngayBatDau,
        danhSachKhach
    });

    if (result.success) {
        showNotification(result.message, 'success');
        loadPage('phieu-thue/danh-sach');
    } else {
        showNotification(result.message, 'error');
    }
}

// ========== YC3: TRA CỨU PHÒNG ==========
function renderTraCuuPhong() {
    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Tra Cứu Phòng (BM3)</h2>
        </div>

        <div class="search-container">
            <div class="form-row">
                <div class="form-group">
                    <label>Loại Phòng</label>
                    <select id="searchLoaiPhong" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="A">Loại A</option>
                        <option value="B">Loại B</option>
                        <option value="C">Loại C</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng Thái</label>
                    <select id="searchTrangThai" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="TRONG">Phòng trống</option>
                        <option value="DANG_THUE">Đang thuê</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" onclick="executeTraCuu()">Tìm Kiếm</button>
        </div>

        <div id="ketQuaTraCuu" class="results-grid"></div>
    `;
}

function executeTraCuu() {
    const loaiPhong = document.getElementById('searchLoaiPhong').value;
    const trangThai = document.getElementById('searchTrangThai').value;

    const result = phongController.traCuuPhong({ loaiPhong, trangThai });
    hienThiKetQuaTraCuu(result.data);
}

function hienThiKetQuaTraCuu(danhSach) {
    const container = document.getElementById('ketQuaTraCuu');
    container.innerHTML = '';

    if (danhSach.length === 0) {
        container.innerHTML = '<p class="no-result">Không tìm thấy phòng phù hợp</p>';
        return;
    }

    danhSach.forEach(phong => {
        const card = document.createElement('div');
        card.className = 'room-card';
        card.innerHTML = `
            <h3>${phong.tenPhong}</h3>
            <p><strong>Loại:</strong> ${phong.loaiPhong}</p>
            <p><strong>Đơn giá:</strong> ${formatCurrency(phong.donGia)}</p>
            <p><strong>Tình trạng:</strong> <span class="badge ${phong.trangThai === 'TRONG' ? 'badge-success' : 'badge-warning'}">
                ${phong.trangThai === 'TRONG' ? 'Phòng trống' : 'Đang thuê'}
            </span></p>
            ${phong.ghiChu ? `<p><strong>Ghi chú:</strong> ${phong.ghiChu}</p>` : ''}
        `;
        container.appendChild(card);
    });
}

// ========== YC4: LẬP HÓA ĐƠN THANH TOÁN ==========
function renderDanhSachHoaDon() {
    const danhSach = hoaDonController.hienThiDanhSachHoaDon();
    
    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Danh Sách Hóa Đơn (BM4)</h2>
            <button class="btn btn-primary" onclick="loadPage('hoa-don/tao-hoa-don')">+ Tạo Hóa Đơn</button>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã HĐ</th>
                    <th>Khách hàng</th>
                    <th>Ngày Lập</th>
                    <th>Trị Giá</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody id="tbodyHoaDon"></tbody>
        </table>
    `;

    const tbody = document.getElementById('tbodyHoaDon');
    danhSach.forEach(hd => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${hd.maHoaDon}</td>
            <td>${hd.khachHang}</td>
            <td>${formatDate(hd.ngayLap)}</td>
            <td><strong>${formatCurrency(hd.triGia)}</strong></td>
            <td>
                <button class="btn-action btn-edit" onclick="xemChiTietHoaDon('${hd.maHoaDon}')">Chi tiết</button>
            </td>
        `;
    });
}

function xemChiTietHoaDon(maHoaDon) {
    const result = hoaDonController.layChiTietHoaDon(maHoaDon);
    if (result.success) {
        alert(JSON.stringify(result.data, null, 2));
    }
}

function renderTaoHoaDon() {
    const data = hoaDonController.hienThiFormTaoHoaDon();

    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Lập Hóa Đơn Thanh Toán (BM4)</h2>
        </div>

        <form id="formHoaDon" class="form-container">
            <div class="form-group">
                <label for="phieuThue">Chọn Phiếu Thuê <span class="required">*</span></label>
                <select id="phieuThue" name="phieuThue" class="form-control" required>
                    <option value="">-- Chọn phiếu thuê --</option>
                </select>
            </div>

            <div id="thongTinPhieuThue" class="info-box" style="display:none;">
                <h3>Thông Tin Phiếu Thuê</h3>
                <div class="info-row">
                    <span class="label">Phòng:</span>
                    <span id="infoPhong"></span>
                </div>
                <div class="info-row">
                    <span class="label">Loại Phòng:</span>
                    <span id="infoLoaiPhong"></span>
                </div>
                <div class="info-row">
                    <span class="label">Đơn Giá Cơ Bản:</span>
                    <span id="infoDonGia"></span>
                </div>
                <div class="info-row">
                    <span class="label">Ngày Thuê:</span>
                    <span id="infoNgayThue"></span>
                </div>
                <div class="info-row">
                    <span class="label">Số Ngày Thuê:</span>
                    <span id="infoSoNgay"></span>
                </div>

                <h4>Danh Sách Khách</h4>
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Tên Khách</th>
                            <th>Loại Khách</th>
                            <th>CMND</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyChiTietKhach"></tbody>
                </table>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="khachHang">Khách Hàng/Cơ Quan <span class="required">*</span></label>
                    <input type="text" id="khachHang" name="khachHang" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="diaChi">Địa Chỉ <span class="required">*</span></label>
                    <input type="text" id="diaChi" name="diaChi" class="form-control" required>
                </div>
            </div>

            <div id="tinhToanBox" class="calculation-box" style="display:none;">
                <h3>Tính Toán Hóa Đơn (QĐ4)</h3>
                <div class="calc-row">
                    <span>Đơn Giá Cơ Bản:</span>
                    <span id="calcDonGiaCoBan">0 VNĐ</span>
                </div>
                <div class="calc-row highlight">
                    <span>Phụ Thu Khách Thứ 3 (25%):</span>
                    <span id="calcPhuThu">0 VNĐ</span>
                </div>
                <div class="calc-row highlight">
                    <span>Hệ Số Khách Nước Ngoài (×1.5):</span>
                    <span id="calcHeSo">×1</span>
                </div>
                <div class="calc-row">
                    <span>Đơn Giá Điều Chỉnh:</span>
                    <span id="calcDonGiaDieuChinh">0 VNĐ</span>
                </div>
                <div class="calc-row">
                    <span>Số Ngày Thuê:</span>
                    <span id="calcSoNgay">0</span>
                </div>
                <div class="calc-row total">
                    <span>THÀNH TIỀN:</span>
                    <span id="calcThanhTien">0 VNĐ</span>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Quy định (QĐ4):</strong><br>
                - Phụ thu 25% nếu có khách thứ 3 trở lên<br>
                - Nhân hệ số 1.5 nếu có ít nhất 1 khách nước ngoài
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu Hóa Đơn</button>
                <button type="button" class="btn btn-secondary" onclick="loadPage('hoa-don/danh-sach')">Hủy</button>
            </div>
        </form>
    `;

    // Load phiếu đang thuê
    const selectPhieu = document.getElementById('phieuThue');
    data.danhSachPhieu.forEach(phieu => {
        const phong = phongService.layPhong(phieu.maPhong);
        const option = document.createElement('option');
        option.value = phieu.maPhieuThue;
        option.textContent = `${phieu.maPhieuThue} - ${phong.tenPhong} (${formatDate(phieu.ngayBatDau)})`;
        selectPhieu.appendChild(option);
    });

    // Khi chọn phiếu
    selectPhieu.addEventListener('change', function() {
        const maPhieuThue = this.value;
        if (!maPhieuThue) {
            document.getElementById('thongTinPhieuThue').style.display = 'none';
            document.getElementById('tinhToanBox').style.display = 'none';
            return;
        }
        hienThiThongTinPhieuThue(maPhieuThue);
    });

    // Submit
    document.getElementById('formHoaDon').addEventListener('submit', submitHoaDon);
}

function hienThiThongTinPhieuThue(maPhieuThue) {
    const result = hoaDonController.tinhChiTietThanhToan(maPhieuThue);
    
    if (!result.success) {
        showNotification(result.message, 'error');
        return;
    }

    const data = result.data;

    // Hiển thị thông tin
    document.getElementById('thongTinPhieuThue').style.display = 'block';
    document.getElementById('tinhToanBox').style.display = 'block';
    
    document.getElementById('infoPhong').textContent = data.phong.tenPhong;
    document.getElementById('infoLoaiPhong').textContent = data.phong.loaiPhong;
    document.getElementById('infoDonGia').textContent = formatCurrency(data.donGiaCoBan);
    document.getElementById('infoNgayThue').textContent = formatDate(data.phieuThue.ngayBatDau);
    document.getElementById('infoSoNgay').textContent = data.soNgayThue + ' ngày';

    // Danh sách khách
    const tbody = document.getElementById('tbodyChiTietKhach');
    tbody.innerHTML = '';
    data.phieuThue.danhSachKhach.forEach(khach => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${khach.tenKhach}</td>
            <td>${khach.loaiKhach === 'NOI_DIA' ? 'Nội địa' : 'Nước ngoài'}</td>
            <td>${khach.cmnd}</td>
        `;
    });

    // Tính toán
    document.getElementById('calcDonGiaCoBan').textContent = formatCurrency(data.donGiaCoBan);
    
    if (data.apDungPhuThu) {
        document.getElementById('calcPhuThu').textContent = 
            `${formatCurrency(data.donGiaCoBan * data.phuThu)} (${data.phuThu * 100}%)`;
    } else {
        document.getElementById('calcPhuThu').textContent = 'Không áp dụng';
    }

    document.getElementById('calcHeSo').textContent = `×${data.apDungHeSo}`;
    document.getElementById('calcDonGiaDieuChinh').textContent = formatCurrency(data.donGiaDieuChinh);
    document.getElementById('calcSoNgay').textContent = data.soNgayThue;
    document.getElementById('calcThanhTien').textContent = formatCurrency(data.thanhTien);
}

function submitHoaDon(e) {
    e.preventDefault();

    const formData = {
        maPhieuThue: document.getElementById('phieuThue').value,
        khachHang: document.getElementById('khachHang').value,
        diaChi: document.getElementById('diaChi').value,
        ngayKetThuc: new Date().toISOString().split('T')[0]
    };

    const result = hoaDonController.lapHoaDon(formData);

    if (result.success) {
        showNotification(result.message, 'success');
        loadPage('hoa-don/danh-sach');
    } else {
        showNotification(result.message, 'error');
    }
}

// ========== YC5: BÁO CÁO THÁNG ==========
function renderBaoCaoThang() {
    const now = new Date();
    const thangHienTai = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Báo Cáo Doanh Thu Tháng (BM5)</h2>
        </div>

        <div class="filter-section">
            <div class="form-row">
                <div class="form-group">
                    <label for="thangBaoCao">Chọn Tháng</label>
                    <input type="month" id="thangBaoCao" class="form-control" value="${thangHienTai}">
                </div>
                <button class="btn btn-primary" onclick="executeXemBaoCao()">Xem Báo Cáo</button>
                <button class="btn btn-secondary" onclick="xuatBaoCaoPDF()">Xuất PDF</button>
            </div>
        </div>

        <div id="baoCaoContent" class="report-container">
            <div class="report-header">
                <h3>KHÁCH SẠN ABC</h3>
                <h4>BÁO CÁO DOANH THU THÁNG <span id="titleThang"></span></h4>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Loại Phòng</th>
                        <th>Doanh Thu (VNĐ)</th>
                        <th>Tỷ Lệ (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Loại A</td>
                        <td class="text-right" id="doanhThuA">0</td>
                        <td class="text-right" id="tyLeA">0%</td>
                    </tr>
                    <tr>
                        <td>Loại B</td>
                        <td class="text-right" id="doanhThuB">0</td>
                        <td class="text-right" id="tyLeB">0%</td>
                    </tr>
                    <tr>
                        <td>Loại C</td>
                        <td class="text-right" id="doanhThuC">0</td>
                        <td class="text-right" id="tyLeC">0%</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Tổng Cộng</strong></td>
                        <td class="text-right" id="tongDoanhThu"><strong>0</strong></td>
                        <td class="text-right"><strong>100%</strong></td>
                    </tr>
                </tbody>
            </table>

            <div id="chartContainer" class="chart-container">
                <canvas id="doanhThuChart" width="600" height="400"></canvas>
            </div>
        </div>
    `;

    // Auto load báo cáo tháng hiện tại
    executeXemBaoCao();
}

function executeXemBaoCao() {
    const thangInput = document.getElementById('thangBaoCao').value;
    if (!thangInput) {
        showNotification('Vui lòng chọn tháng', 'warning');
        return;
    }

    const [nam, thang] = thangInput.split('-');
    const result = baoCaoController.lapBaoCaoThang(parseInt(thang), parseInt(nam));

    if (!result.success) {
        showNotification(result.message, 'error');
        return;
    }

    const baoCao = result.data;

    document.getElementById('titleThang').textContent = `${thang}/${nam}`;
    document.getElementById('doanhThuA').textContent = formatCurrency(baoCao.doanhThu.A);
    document.getElementById('tyLeA').textContent = baoCao.tyLe.A + '%';
    document.getElementById('doanhThuB').textContent = formatCurrency(baoCao.doanhThu.B);
    document.getElementById('tyLeB').textContent = baoCao.tyLe.B + '%';
    document.getElementById('doanhThuC').textContent = formatCurrency(baoCao.doanhThu.C);
    document.getElementById('tyLeC').textContent = baoCao.tyLe.C + '%';
    document.getElementById('tongDoanhThu').textContent = formatCurrency(baoCao.tongDoanhThu);

    veChart(baoCao);
}

function veChart(baoCao) {
    const canvas = document.getElementById('doanhThuChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    const maxValue = Math.max(baoCao.doanhThu.A, baoCao.doanhThu.B, baoCao.doanhThu.C) || 1;
    const barWidth = 80;
    const barSpacing = 100;
    const chartHeight = 300;
    const startX = 100;
    const startY = 50;

    const data = [
        { label: 'Loại A', value: baoCao.doanhThu.A, color: '#3498db' },
        { label: 'Loại B', value: baoCao.doanhThu.B, color: '#2ecc71' },
        { label: 'Loại C', value: baoCao.doanhThu.C, color: '#e74c3c' }
    ];

    data.forEach((item, index) => {
        const barHeight = (item.value / maxValue) * chartHeight;
        const x = startX + index * (barWidth + barSpacing);
        const y = startY + chartHeight - barHeight;

        ctx.fillStyle = item.color;
        ctx.fillRect(x, y, barWidth, barHeight);

        ctx.fillStyle = '#333';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(item.label, x + barWidth / 2, startY + chartHeight + 20);
        ctx.fillText(formatCurrency(item.value), x + barWidth / 2, y - 10);
    });
}

function xuatBaoCaoPDF() {
    showNotification('Chức năng xuất PDF đang được phát triển', 'info');
}

// ========== YC6: QUẢN LÝ THAM SỐ ==========
function renderQuanLyThamSo() {
    const ts = thamSoController.layThamSo();

    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>Quản Lý Tham Số (QĐ6)</h2>
        </div>

        <div class="alert alert-warning">
            <strong>Lưu ý:</strong> Thay đổi tham số sẽ ảnh hưởng đến tất cả các tính toán trong hệ thống.
        </div>

        <form id="formThamSo" class="form-container">
            <h3 class="section-title">Đơn Giá Phòng (QĐ1)</h3>
            
            <div class="form-group">
                <label for="donGiaA">Đơn Giá Loại A (VNĐ)</label>
                <input type="number" id="donGiaA" name="donGiaA" class="form-control" value="${ts.donGiaPhong.A}" min="0">
            </div>

            <div class="form-group">
                <label for="donGiaB">Đơn Giá Loại B (VNĐ)</label>
                <input type="number" id="donGiaB" name="donGiaB" class="form-control" value="${ts.donGiaPhong.B}" min="0">
            </div>

            <div class="form-group">
                <label for="donGiaC">Đơn Giá Loại C (VNĐ)</label>
                <input type="number" id="donGiaC" name="donGiaC" class="form-control" value="${ts.donGiaPhong.C}" min="0">
            </div>

            <h3 class="section-title">Quy Định Khách (QĐ2)</h3>

            <div class="form-group">
                <label for="soKhachToiDa">Số Khách Tối Đa/Phòng</label>
                <input type="number" id="soKhachToiDa" name="soKhachToiDa" class="form-control" value="${ts.soKhachToiDa}" min="1" max="10">
            </div>

            <h3 class="section-title">Hệ Số Tính Toán (QĐ4)</h3>

            <div class="form-group">
                <label for="phuThuKhach3">Phụ Thu Khách Thứ 3 (%)</label>
                <input type="number" id="phuThuKhach3" name="phuThuKhach3" class="form-control" value="${ts.phuThuKhach3 * 100}" min="0" max="100">
                <small class="form-text">Tỷ lệ phụ thu khi có khách thứ 3 trở lên</small>
            </div>

            <div class="form-group">
                <label for="heSoNuocNgoai">Hệ Số Khách Nước Ngoài</label>
                <input type="number" id="heSoNuocNgoai" name="heSoNuocNgoai" class="form-control" value="${ts.heSoNuocNgoai}" min="1" step="0.1">
                <small class="form-text">Hệ số nhân khi có ít nhất 1 khách nước ngoài</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cập Nhật Tham Số</button>
                <button type="button" class="btn btn-secondary" onclick="khoiPhucThamSo()">Khôi Phục Mặc Định</button>
            </div>
        </form>

        <div class="info-box">
            <h3>Thông Tin</h3>
            <p><strong>Lần Cập Nhật Cuối:</strong> <span id="lanCapNhatCuoi">${new Date().toLocaleString('vi-VN')}</span></p>
            <p><strong>Người Cập Nhật:</strong> <span id="nguoiCapNhat">Admin</span></p>
        </div>
    `;

    document.getElementById('formThamSo').addEventListener('submit', submitThamSo);
}

function submitThamSo(e) {
    e.preventDefault();

    const formData = {
        donGiaA: document.getElementById('donGiaA').value,
        donGiaB: document.getElementById('donGiaB').value,
        donGiaC: document.getElementById('donGiaC').value,
        soKhachToiDa: document.getElementById('soKhachToiDa').value,
        phuThuKhach3: document.getElementById('phuThuKhach3').value,
        heSoNuocNgoai: document.getElementById('heSoNuocNgoai').value
    };

    const result = thamSoController.capNhatThamSo(formData);

    if (result.success) {
        showNotification(result.message, 'success');
        document.getElementById('lanCapNhatCuoi').textContent = new Date().toLocaleString('vi-VN');
    } else {
        showNotification(result.message, 'error');
    }
}

function khoiPhucThamSo() {
    if (confirm('Bạn có chắc chắn muốn khôi phục tham số mặc định?')) {
        const result = thamSoController.khoiPhucMacDinh();
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => {
                loadPage('tham-so/quan-ly-tham-so');
            }, 1000);
        }
    }
}

// ========== DEV TOOLS ==========
function renderDevTools() {
    const content = document.getElementById('content');
    content.innerHTML = `
        <div class="page-header">
            <h2>🛠️ Developer Tools</h2>
        </div>

        <div class="dev-tools-container">
            <div class="tool-section">
                <h3>Dữ Liệu Mẫu</h3>
                <button class="btn btn-primary" onclick="initSampleData()">
                    🔄 Tạo Dữ Liệu Mẫu
                </button>
                <p class="form-text">Tạo 6 phòng mẫu để test hệ thống</p>
            </div>

            <div class="tool-section">
                <h3>LocalStorage</h3>
                <button class="btn btn-secondary" onclick="viewLocalStorage()">
                    👁️ Xem Dữ Liệu
                </button>
                <button class="btn btn-delete" onclick="clearAllData()">
                    🗑️ Xóa Tất Cả Dữ Liệu
                </button>
            </div>

            <div class="tool-section">
                <h3>Thống Kê Hệ Thống</h3>
                <div id="systemStats"></div>
            </div>

            <div class="tool-section">
                <h3>Test Quy Định</h3>
                <button class="btn btn-primary" onclick="testQuyDinh()">
                    🧪 Test Tất Cả Quy Định
                </button>
                <pre id="testResults"></pre>
            </div>
        </div>
    `;

    loadSystemStats();
}

function viewLocalStorage() {
    const data = {
        phong: localStorage.getItem('danhsach_phong'),
        phieuthue: localStorage.getItem('danhsach_phieuthue'),
        hoadon: localStorage.getItem('danhsach_hoadon'),
        thamso: localStorage.getItem('thamso')
    };

    console.log('📦 LocalStorage Data:', data);
    alert('Đã xuất dữ liệu ra Console (F12)');
}

function loadSystemStats() {
    const stats = {
        tongPhong: phongService.layDanhSachPhong().length,
        phongTrong: phongService.layPhongTrong().length,
        tongPhieu: phieuThueService.layDanhSachPhieu().length,
        phieuDangThue: phieuThueService.layPhieuDangThue().length,
        tongHoaDon: hoaDonService.layDanhSachHoaDon({}).length
    };

    document.getElementById('systemStats').innerHTML = `
        <table class="mini-table">
            <tr><td>Tổng số phòng:</td><td><strong>${stats.tongPhong}</strong></td></tr>
            <tr><td>Phòng trống:</td><td><strong>${stats.phongTrong}</strong></td></tr>
            <tr><td>Tổng phiếu thuê:</td><td><strong>${stats.tongPhieu}</strong></td></tr>
            <tr><td>Đang thuê:</td><td><strong>${stats.phieuDangThue}</strong></td></tr>
            <tr><td>Tổng hóa đơn:</td><td><strong>${stats.tongHoaDon}</strong></td></tr>
        </table>
    `;
}

function testQuyDinh() {
    const results = [];

    // Test QĐ1: 3 loại phòng
    const ts = thamSo.getAll();
    results.push('✅ QĐ1: 3 loại phòng - OK');
    results.push(`   A: ${ts.donGiaPhong.A}, B: ${ts.donGiaPhong.B}, C: ${ts.donGiaPhong.C}`);

    // Test QĐ2: Số khách tối đa
    results.push(`✅ QĐ2: Số khách tối đa ${ts.soKhachToiDa} - OK`);

    // Test QĐ4: Phụ thu và hệ số
    results.push(`✅ QĐ4: Phụ thu ${ts.phuThuKhach3*100}%, Hệ số ${ts.heSoNuocNgoai} - OK`);

    document.getElementById('testResults').textContent = results.join('\n');
}

// ========== UTILITY FUNCTIONS ==========
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}