<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../controllers/KhachHangController.php';

$database = new Database();
$db = $database->connect();
$phieuThueCtrl = new PhieuThueController();
$khachHangCtrl = new KhachHangController();

$message = '';
$error = '';
$maPhieuThue = null;

// Xử lý đặt phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Thêm khách hàng vào database
        $danhSachKhach = [];
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_POST["tenKhach$i"])) {
                $maKhach = $khachHangCtrl->themKhachHang(
                    $_POST["tenKhach$i"],
                    $_POST["loaiKhach$i"],
                    $_POST["cmnd$i"],
                    $_POST["diaChi$i"]
                );
                $danhSachKhach[] = $maKhach;
            }
        }
        
        // Tạo phiếu thuê
        $maPhieuThue = $phieuThueCtrl->taoPhieuThue(
            $_POST['maPhong'],
            $_POST['ngayBatDau'],
            $danhSachKhach
        );
        
        $message = "Đặt phòng thành công! Mã phiếu thuê của bạn là: #$maPhieuThue";
    } catch (Exception $e) {
        $error = "Lỗi đặt phòng: " . $e->getMessage();
    }
}

// Lấy thông tin phòng
if (!isset($_GET['phong'])) {
    header('Location: ../index.php');
    exit;
}

$maPhong = $_GET['phong'];
$stmt = $db->prepare("SELECT P.*, L.TenLoai, L.DonGiaCoBan FROM PHONG P 
                      JOIN LOAIPHONG L ON P.MaLoaiPhong = L.MaLoaiPhong 
                      WHERE P.MaPhong = ?");
$stmt->execute([$maPhong]);
$phong = $stmt->fetch();

if (!$phong || $phong['TinhTrang'] !== 'Trống') {
    header('Location: ../index.php');
    exit;
}

$soKhachToiDa = $database->getThamSo('SO_KHACH_TOI_DA');
$tlPhuThu = $database->getThamSo('TL_PHU_THU_KHACH_3');
$hsKhachNN = $database->getThamSo('HS_KHACH_NUOC_NGOAI');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt Phòng <?= $phong['SoPhong'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/datphong.css">
    <style>
        .booking-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .khach-section {
            border: 2px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            background: #f8f9ff;
        }
        .khach-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .required-label::after {
            content: " *";
            color: red;
        }
        .room-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .room-summary h2 {
            margin-bottom: 15px;
        }
        .room-summary .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .room-summary .info-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
        }
        .room-summary .info-item strong {
            display: block;
            font-size: 1.3em;
            margin-top: 5px;
        }
        .success-message {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin: 30px 0;
        }
        .success-message h2 {
            color: #28a745;
            margin-bottom: 15px;
        }
        .success-message .booking-code {
            font-size: 2em;
            font-weight: bold;
            color: #28a745;
            margin: 20px 0;
        }
        .toggle-khach {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .toggle-khach:hover {
            background: #5568d3;
        }
        .khach-section.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-content">
                <h1>📝 Đặt Phòng Trực Tuyến</h1>
                <p>Hoàn tất thông tin để đặt phòng</p>
            </div>
            <nav class="booking-nav">
                <a href="../index.php">🏠 Trang chủ</a>
                <a href="tra-cuu-dat-phong.php">🔍 Tra cứu đặt phòng</a>
            </nav>
        </header>

        <main class="booking-main">
            <?php if ($maPhieuThue): ?>
            <!-- Thông báo đặt phòng thành công -->
            <div class="success-message">
                <h2>✅ Đặt Phòng Thành Công!</h2>
                <p>Cảm ơn bạn đã đặt phòng tại khách sạn của chúng tôi.</p>
                <div class="booking-code">Mã Phiếu Thuê: #<?= $maPhieuThue ?></div>
                <p><strong>Vui lòng lưu lại mã này để tra cứu và check-in.</strong></p>
                
                <div style="margin-top: 30px;">
                    <p>📅 Ngày nhận phòng: <strong><?= date('d/m/Y', strtotime($_POST['ngayBatDau'])) ?></strong></p>
                    <p>🛏️ Phòng: <strong><?= $phong['SoPhong'] ?> - <?= $phong['TenLoai'] ?></strong></p>
                    <p>💰 Đơn giá: <strong><?= number_format($phong['DonGiaCoBan']) ?>đ/đêm</strong></p>
                </div>

                <div style="margin-top: 30px;">
                    <a href="../index.php" class="btn" style="background: white; color: #667eea; margin-right: 10px;">← Về trang chủ</a>
                    <a href="tra-cuu-dat-phong.php?keyword=<?= $maPhieuThue ?>&search=1" class="btn">🔍 Xem chi tiết đặt phòng</a>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <!-- Thông tin phòng -->
            <div class="room-summary">
                <h2>🛏️ Thông Tin Phòng Đã Chọn</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span>Số Phòng</span>
                        <strong><?= $phong['SoPhong'] ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Loại Phòng</span>
                        <strong><?= $phong['TenLoai'] ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Đơn Giá</span>
                        <strong><?= number_format($phong['DonGiaCoBan']) ?>đ/đêm</strong>
                    </div>
                    <div class="info-item">
                        <span>Sức Chứa</span>
                        <strong>Tối đa <?= $soKhachToiDa ?> khách</strong>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <div class="estimate-box" role="status" aria-live="polite">
                        <div>💸 Giá ước tính:</div>
                        <div class="estimate-value" id="estimateValue"><?= number_format($phong['DonGiaCoBan']) ?>đ</div>
                    </div>
                </div>
                <?php if ($phong['GhiChu']): ?>
                <p style="margin-top: 15px; font-style: italic;">📝 <?= $phong['GhiChu'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Form đặt phòng -->
            <div class="booking-form">
                <h2>📋 Thông Tin Đặt Phòng</h2>
                
                <form method="POST" id="formDatPhong">
                    <input type="hidden" name="maPhong" value="<?= $phong['MaPhong'] ?>">
                    
                    <div class="form-group">
                        <label class="required-label">Ngày Nhận Phòng:</label>
                        <input type="date" name="ngayBatDau" 
                               value="<?= date('Y-m-d') ?>" 
                               min="<?= date('Y-m-d') ?>"
                               required>
                        <small>Giờ nhận phòng: 14:00. Giờ trả phòng: 12:00</small>
                    </div>

                    <hr style="margin: 30px 0;">

                    <h2>👥 Thông Tin Khách Hàng</h2>
                    <p style="color: #666; margin-bottom: 20px;">
                        <strong>Lưu ý:</strong> Tối đa <?= $soKhachToiDa ?> khách/phòng. 
                        Khách thứ 3 sẽ phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%.
                    </p>

                    <!-- Khách 1 (bắt buộc) -->
                    <div class="khach-section">
                        <h3>
                            <span>👤</span>
                            <span>Khách Hàng 1 (Người đặt phòng)</span>
                            <span style="color: red; font-size: 0.9em;">*Bắt buộc</span>
                        </h3>
                        
                        <div class="form-group">
                            <label class="required-label">Họ và Tên:</label>
                            <input type="text" name="tenKhach1" required 
                                   placeholder="Nguyễn Văn A">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="required-label">Loại Khách:</label>
                                <select name="loaiKhach1" required>
                                    <option value="Nội địa">🇻🇳 Nội địa</option>
                                    <option value="Nước ngoài">🌍 Nước ngoài (Hệ số ×<?= $database->getThamSo('HS_KHACH_NUOC_NGOAI') ?>)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="required-label">CMND/CCCD/Passport:</label>
                                <input type="text" name="cmnd1" required 
                                       placeholder="123456789">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required-label">Địa Chỉ:</label>
                            <input type="text" name="diaChi1" required 
                                   placeholder="Số nhà, Đường, Quận/Huyện, Tỉnh/TP">
                        </div>
                    </div>

                    <!-- Khách 2 (tùy chọn) -->
                    <div class="khach-section hidden" id="khach2Section">
                        <h3>
                            <span>👤</span>
                            <span>Khách Hàng 2</span>
                            <span style="color: #999; font-size: 0.9em;">Tùy chọn</span>
                        </h3>
                        
                        <div class="form-group">
                            <label>Họ và Tên:</label>
                            <input type="text" name="tenKhach2" 
                                   placeholder="Trần Thị B">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Loại Khách:</label>
                                <select name="loaiKhach2">
                                    <option value="Nội địa">🇻🇳 Nội địa</option>
                                    <option value="Nước ngoài">🌍 Nước ngoài</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>CMND/CCCD/Passport:</label>
                                <input type="text" name="cmnd2" 
                                       placeholder="987654321">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Địa Chỉ:</label>
                            <input type="text" name="diaChi2" 
                                   placeholder="Số nhà, Đường, Quận/Huyện, Tỉnh/TP">
                        </div>
                    </div>

                    <!-- Khách 3 (tùy chọn - có phụ thu) -->
                    <div class="khach-section hidden" id="khach3Section">
                        <h3>
                            <span>👤</span>
                            <span>Khách Hàng 3</span>
                            <span style="color: #ff9800; font-size: 0.9em;">⚠️ Phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%</span>
                        </h3>
                        
                        <div class="form-group">
                            <label>Họ và Tên:</label>
                            <input type="text" name="tenKhach3" 
                                   placeholder="Lê Văn C">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Loại Khách:</label>
                                <select name="loaiKhach3">
                                    <option value="Nội địa">🇻🇳 Nội địa</option>
                                    <option value="Nước ngoài">🌍 Nước ngoài</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>CMND/CCCD/Passport:</label>
                                <input type="text" name="cmnd3" 
                                       placeholder="456789123">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Địa Chỉ:</label>
                            <input type="text" name="diaChi3" 
                                   placeholder="Số nhà, Đường, Quận/Huyện, Tỉnh/TP">
                        </div>
                    </div>

                    <!-- Nút thêm khách -->
                    <div style="text-align: center; margin: 20px 0;">
                        <button type="button" class="toggle-khach" id="btnKhach2" onclick="toggleKhach(2)">
                            ➕ Thêm Khách Hàng 2
                        </button>
                        <button type="button" class="toggle-khach hidden" id="btnKhach3" onclick="toggleKhach(3)">
                            ➕ Thêm Khách Hàng 3 (Phụ thu <?= $database->getThamSo('TL_PHU_THU_KHACH_3') * 100 ?>%)
                        </button>
                    </div>

                    <hr style="margin: 30px 0;">

                    <!-- Điều khoản -->
                    <div class="form-group">
                        <label>
                            <input type="checkbox" required>
                            Tôi đã đọc và đồng ý với <a href="#" style="color: #667eea;">điều khoản sử dụng</a>
                        </label>
                    </div>

                    <!-- Nút submit -->
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <a href="../index.php" class="btn" style="background: #999; flex: 1; text-align: center;">
                            ← Quay lại
                        </a>
                        <button type="submit" class="btn" style="flex: 2;">
                            ✅ Xác Nhận Đặt Phòng
                        </button>
                    </div>
                </form>
            </div>

            <?php endif; ?>
        </main>

        <footer class="booking-footer">
            <p>&copy; 2024 Khách sạn - Hệ thống đặt phòng trực tuyến</p>
        </footer>
    </div>

    <script>
        function toggleKhach(soKhach) {
            const section = document.getElementById('khach' + soKhach + 'Section');
            const btn = document.getElementById('btnKhach' + soKhach);
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                btn.textContent = '➖ Bỏ Khách Hàng ' + soKhach;
                btn.style.background = '#dc3545';
                
                // Hiện nút thêm khách tiếp theo
                if (soKhach === 2) {
                    document.getElementById('btnKhach3').classList.remove('hidden');
                }
            } else {
                section.classList.add('hidden');
                btn.textContent = '➕ Thêm Khách Hàng ' + soKhach;
                btn.style.background = '#667eea';
                
                // Clear input values
                section.querySelectorAll('input, select').forEach(input => {
                    if (input.type !== 'hidden') {
                        input.value = input.tagName === 'SELECT' ? 'Nội địa' : '';
                    }
                });
                
                // Ẩn nút thêm khách tiếp theo
                if (soKhach === 2) {
                    document.getElementById('btnKhach3').classList.add('hidden');
                    document.getElementById('khach3Section').classList.add('hidden');
                }
            }
            updateEstimate();
        }

+        // Tính ước lượng tiền phòng dựa trên tham số hệ thống và số khách
+        const donGiaCoBan = <?= json_encode(floatval($phong['DonGiaCoBan'])) ?>;
+        const soKhachToiDaParam = <?= json_encode(intval($soKhachToiDa)) ?>;
+        const tlPhuThu = <?= json_encode(floatval($tlPhuThu)) ?>;
+        const hsKhachNN = <?= json_encode(floatval($hsKhachNN)) ?>;
+
+        function currencyFormat(n) {
+            return n.toLocaleString('vi-VN') + 'đ';
+        }
+
+        function getActiveGuestCount() {
+            let count = 0;
+            for (let i = 1; i <= 3; i++) {
+                const name = document.querySelector('input[name="tenKhach' + i + '"]');
+                if (name && name.value.trim() !== '') count++;
+            }
+            return count;
+        }
+
+        function hasForeignGuest() {
+            for (let i = 1; i <= 3; i++) {
+                const select = document.querySelector('select[name="loaiKhach' + i + '"]');
+                if (select && select.value === 'Nước ngoài') return true;
+            }
+            return false;
+        }
+
+        function updateEstimate() {
+            const days = 1; // Default 1 đêm (không có ngày trả)
+            let price = donGiaCoBan;
+
+            const activeGuests = getActiveGuestCount() || 1;
+            if (activeGuests >= soKhachToiDaParam) {
+                price *= (1 + tlPhuThu);
+            }
+            if (hasForeignGuest()) price *= hsKhachNN;
+
+            const total = Math.round(price * days);
+            document.getElementById('estimateValue').textContent = currencyFormat(total);
+        }
+
+        // Các sự kiện thay đổi để cập nhật ước lượng
+        document.querySelectorAll('select[name^="loaiKhach"], input[name^="tenKhach"]').forEach(el => {
+            el.addEventListener('change', updateEstimate);
+            el.addEventListener('input', updateEstimate);
+        });
+        document.querySelector('input[name="ngayBatDau"]').addEventListener('change', updateEstimate);
+
+        // Đặt ước lượng khi tải trang
+        updateEstimate();
+
+        // Validate form trước khi submit
+        document.getElementById('formDatPhong').addEventListener('submit', function(e) {
+            const ngayBatDau = new Date(document.querySelector('input[name="ngayBatDau"]').value);
+            const today = new Date();
+            today.setHours(0, 0, 0, 0);
+            
+            if (ngayBatDau < today) {
+                e.preventDefault();
+                alert('Ngày nhận phòng phải từ hôm nay trở đi!');
+                return false;
+            }
+            
+            return confirm('Xác nhận đặt phòng với thông tin đã nhập?');
+        });
    </script>
</body>
</html>
