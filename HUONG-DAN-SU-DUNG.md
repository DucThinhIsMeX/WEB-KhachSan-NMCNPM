# HƯỚNG DẪN SỬ DỤNG HỆ THỐNG QUẢN LÝ KHÁCH SẠN

## 🚀 KHỞI ĐỘNG HỆ THỐNG

### Bước 1: Mở ứng dụng
- Mở file `index.html` bằng trình duyệt (Chrome, Firefox, Edge)
- Hoặc sử dụng Live Server trong VS Code

### Bước 2: Tạo dữ liệu mẫu
1. Click vào menu **🛠️ Dev Tools**
2. Click nút **🔄 Tạo Dữ Liệu Mẫu**
3. Hệ thống sẽ tạo 6 phòng mẫu

---

## 📋 HƯỚNG DẪN THEO TỪNG CHỨC NĂNG

### ✅ YC1: LẬP DANH MỤC PHÒNG (BM1)

**Thêm phòng mới:**
1. Menu → **🏠 Quản lý Phòng**
2. Click **+ Thêm Phòng**
3. Nhập:
   - Mã Phòng (ví dụ: P301)
   - Tên Phòng (ví dụ: Phòng 301)
   - Loại Phòng: A/B/C (đơn giá tự động)
   - Ghi chú (tùy chọn)
4. Click **Lưu**

**Xóa phòng:**
- Click nút **Xóa** ở cột Thao Tác
- Lưu ý: Không xóa được phòng đang thuê

**Lọc phòng:**
- Chọn Loại Phòng hoặc Trạng Thái để lọc

---

### ✅ YC2: LẬP PHIẾU THUÊ PHÒNG (BM2)

**Tạo phiếu thuê:**
1. Menu → **📝 Phiếu Thuê Phòng**
2. Click **+ Thêm Phiếu Thuê**
3. Chọn **Phòng** (chỉ hiện phòng trống)
4. Chọn **Ngày Bắt Đầu Thuê**
5. Nhập thông tin **Khách 1** (bắt buộc):
   - Tên Khách Hàng
   - Loại Khách: Nội địa / Nước ngoài
   - CMND/CCCD
   - Địa Chỉ
6. Click **+ Thêm Khách** để thêm khách 2, 3 (tối đa 3)
7. Click **Lưu Phiếu Thuê**

**Quy định (QĐ2):**
- ⚠️ Mỗi phòng tối đa 3 khách
- ⚠️ Chỉ 2 loại: Nội địa, Nước ngoài

---

### ✅ YC3: TRA CỨU PHÒNG (BM3)

1. Menu → **🔍 Tra cứu Phòng**
2. Chọn bộ lọc:
   - Loại Phòng: A/B/C
   - Trạng Thái: Trống/Đang thuê
3. Click **Tìm Kiếm**
4. Xem kết quả dạng card

---

### ✅ YC4: LẬP HÓA ĐƠN THANH TOÁN (BM4)

**Tạo hóa đơn:**
1. Menu → **💰 Hóa đơn Thanh toán**
2. Click **+ Tạo Hóa Đơn**
3. Chọn **Phiếu Thuê** (chỉ hiện phiếu đang thuê)
4. Hệ thống tự động hiển thị:
   - Thông tin phòng
   - Danh sách khách
   - **Tính toán theo QĐ4:**
     - Đơn giá cơ bản
     - + Phụ thu 25% (nếu có khách thứ 3)
     - × Hệ số 1.5 (nếu có khách nước ngoài)
     - = Đơn giá điều chỉnh
     - × Số ngày thuê
     - = THÀNH TIỀN
5. Nhập **Khách hàng/Cơ quan** và **Địa chỉ**
6. Click **Lưu Hóa Đơn**

**Quy định (QĐ4):**
- ⚠️ Phụ thu 25% cho khách thứ 3
- ⚠️ Nhân 1.5 nếu có khách nước ngoài
- Áp dụng cả 2 nếu đủ điều kiện

---

### ✅ YC5: LẬP BÁO CÁO THÁNG (BM5)

1. Menu → **📊 Báo cáo Tháng**
2. Chọn **Tháng/Năm**
3. Click **Xem Báo Cáo**
4. Xem:
   - Doanh thu theo loại phòng A, B, C
   - Tỷ lệ % của từng loại
   - Tổng doanh thu
   - Biểu đồ cột

---

### ✅ YC6: THAY ĐỔI QUY ĐỊNH (QĐ6)

1. Menu → **⚙️ Quản lý Tham số**
2. Thay đổi:
   - **QĐ1**: Đơn giá phòng A, B, C
   - **QĐ2**: Số khách tối đa/phòng
   - **QĐ4**: 
     - Phụ thu khách thứ 3 (%)
     - Hệ số khách nước ngoài
3. Click **Cập Nhật Tham Số**

**Hoặc click **Khôi Phục Mặc Định** để về giá trị ban đầu

---

## 🛠️ DEVELOPER TOOLS

Menu **🛠️ Dev Tools** cung cấp:
- **Tạo Dữ Liệu Mẫu**: Tạo 6 phòng để test
- **Xem Dữ Liệu**: Xuất LocalStorage ra Console
- **Xóa Tất Cả Dữ Liệu**: Reset hệ thống
- **Thống Kê**: Xem số liệu tổng quan
- **Test Quy Định**: Kiểm tra QĐ1, QĐ2, QĐ4

---

## ⚠️ LƯU Ý

1. **Dữ liệu lưu trong LocalStorage**: 
   - Không xóa cache trình duyệt
   - Dùng Dev Tools để backup/restore

2. **Quy trình nghiệp vụ:**
   ```
   Tạo Phòng → Thuê Phòng → Trả Phòng (Lập HĐ) → Xem Báo Cáo
   ```

3. **Không thể:**
   - Xóa phòng đang thuê
   - Lập HĐ cho phiếu đã thanh toán
   - Vượt quá 3 khách/phòng

---

## 📞 HỖ TRỢ

- Xem Console (F12) để debug
- Dữ liệu lưu tại: `localStorage`
- Reset: Dev Tools → Xóa Tất Cả Dữ Liệu
