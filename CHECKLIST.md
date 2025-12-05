# ✅ CHECKLIST HOÀN THÀNH DỰ ÁN

## 🎯 CÁC YÊU CẦU CHỨC NĂNG

- [ ] **YC1: Lập danh mục phòng (BM1 - QĐ1)**
  - [ ] Thêm phòng với 3 loại A, B, C
  - [ ] Đơn giá tự động theo loại
  - [ ] Hiển thị danh sách, lọc, xóa

- [ ] **YC2: Lập phiếu thuê phòng (BM2 - QĐ2)**
  - [ ] Chọn phòng trống
  - [ ] Thêm tối đa 3 khách
  - [ ] 2 loại khách: Nội địa, Nước ngoài
  - [ ] Validate QĐ2

- [ ] **YC3: Tra cứu phòng (BM3)**
  - [ ] Lọc theo loại phòng
  - [ ] Lọc theo trạng thái
  - [ ] Hiển thị kết quả

- [ ] **YC4: Lập hóa đơn thanh toán (BM4 - QĐ4)**
  - [ ] Chọn phiếu đang thuê
  - [ ] Tính phụ thu 25% (khách thứ 3)
  - [ ] Tính hệ số 1.5 (khách nước ngoài)
  - [ ] Hiển thị công thức tính rõ ràng

- [ ] **YC5: Lập báo cáo tháng (BM5)**
  - [ ] Chọn tháng/năm
  - [ ] Doanh thu theo loại phòng
  - [ ] Tỷ lệ %
  - [ ] Biểu đồ

- [ ] **YC6: Thay đổi quy định (QĐ6)**
  - [ ] Thay đổi đơn giá phòng
  - [ ] Thay đổi số khách tối đa
  - [ ] Thay đổi phụ thu và hệ số
  - [ ] Khôi phục mặc định

## 🏗️ KIẾN TRÚC

- [ ] **MVC 3-Tier**
  - [ ] Model: Phong, PhieuThue, HoaDon, KhachHang, ThamSo
  - [ ] Service: Business Logic
  - [ ] Controller: Điều khiển
  - [ ] View: SPA (Single Page App)

- [ ] **SPA**
  - [ ] Không reload trang
  - [ ] Routing động
  - [ ] Active menu

## 🧪 TEST

- [ ] **Test từng chức năng**
  - [ ] Tạo phòng thành công
  - [ ] Tạo phiếu thuê với 1, 2, 3 khách
  - [ ] Tạo phiếu với khách nước ngoài
  - [ ] Lập hóa đơn với các trường hợp QĐ4
  - [ ] Báo cáo hiển thị đúng
  - [ ] Thay đổi tham số ảnh hưởng hệ thống

- [ ] **Test validation**
  - [ ] Không cho thêm > 3 khách
  - [ ] Không xóa phòng đang thuê
  - [ ] Không lập HĐ 2 lần

## 📚 TÀI LIỆU

- [ ] README.md
- [ ] HUONG-DAN-SU-DUNG.md
- [ ] DEPLOY.md
- [ ] Code có comment đầy đủ
- [ ] Cấu trúc thư mục rõ ràng

## 🚀 DEPLOY

- [ ] **Môi trường Development**
  - [ ] PHP Built-in server hoạt động
  - [ ] Database khởi tạo thành công
  - [ ] Tất cả chức năng test OK

- [ ] **Chuẩn bị Production**
  - [ ] Minify CSS/JS
  - [ ] Optimize images
  - [ ] Setup .htaccess
  - [ ] Remove debug code
  - [ ] Test performance

- [ ] **Deploy Options**
  - [ ] Local (XAMPP/WAMP) ✅
  - [ ] Hosting miễn phí (InfinityFree/000webhost)
  - [ ] VPS/Server (Ubuntu/CentOS)
  - [ ] Cloud (AWS/GCP/Azure)

- [ ] **Security**
  - [ ] Bảo vệ admin area
  - [ ] Validate all inputs
  - [ ] Prevent SQL injection
  - [ ] Setup SSL certificate
  - [ ] Hide error messages

- [ ] **Monitoring**
  - [ ] Error logging
  - [ ] Backup strategy
  - [ ] Performance monitoring
  - [ ] Uptime monitoring

- [ ] **Documentation**
  - [ ] Installation guide
  - [ ] Deployment guide
  - [ ] Troubleshooting guide
  - [ ] API documentation (if any)
