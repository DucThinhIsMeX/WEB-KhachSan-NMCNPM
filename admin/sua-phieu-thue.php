<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhieuThueController.php';
require_once __DIR__ . '/../controllers/PhongController.php';
require_once __DIR__ . '/../config/database.php';

$auth = new AuthController();
$auth->requireAdmin();

$phieuThueCtrl = new PhieuThueController();
$phongCtrl = new PhongController();
$database = new Database();

$message = '';
$error = '';
$maPhieuThue = $_GET['id'] ?? null;

if (!$maPhieuThue) {
    header('Location: phieu-thue.php');
    exit;
}

// Lấy thông tin phiếu thuê
$phieuThue = $phieuThueCtrl->getPhieuThueById($maPhieuThue);
if (!$phieuThue) {
    header('Location: phieu-thue.php');
    exit;
}

// Chỉ cho phép sửa phiếu đang thuê
if ($phieuThue['TinhTrangPhieu'] !== 'Đang thuê') {
    $_SESSION['error'] = "Chỉ có thể sửa phiếu thuê đang hoạt động";
    header('Location: phieu-thue.php');
    exit;
}

// Lấy danh sách khách hiện tại
$khachHienTai = $phieuThueCtrl->getChiTietKhach($maPhieuThue);

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $maPhong = $_POST['maPhong'];
        $ngayBatDau = $_POST['ngayBatDau'];
        $soDem = intval($_POST['soDem']);
        
        // Lấy danh sách khách từ form
        $danhSachKhach = [];
        $soKhach = intval($_POST['soKhach']);
        
        for ($i = 1; $i <= $soKhach; $i++) {
            if (!empty($_POST["tenKhach$i"])) {
                $danhSachKhach[] = [
                    'tenKhach' => $_POST["tenKhach$i"],
                    'loaiKhach' => $_POST["loaiKhach$i"],
                    'cmnd' => $_POST["cmnd$i"],
                    'diaChi' => $_POST["diaChi$i"] ?? ''
                ];
            }
        }
        
        if (empty($danhSachKhach)) {
            throw new Exception("Phải có ít nhất 1 khách");
        }
        
        $phieuThueCtrl->capNhatPhieuThue($maPhieuThue, $maPhong, $ngayBatDau, $soDem, $danhSachKhach);
        $_SESSION['message'] = "Cập nhật phiếu thuê thành công!";
        header('Location: phieu-thue.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Lấy danh sách phòng trống + phòng hiện tại
$phongsTrong = $phongCtrl->traCuuPhong(null, 'Trống');
$soKhachToiDa = intval($database->getThamSo('SO_KHACH_TOI_DA'));

$page_title = 'Sửa Phiếu Thuê';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Phiếu Thuê</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .khach-section {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        .khach-section:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }
        .khach-section h4 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-remove-khach {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }
        .btn-remove-khach:hover {
            background: #c82333;
            transform: scale(1.1);
        }
        .btn-add-khach {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.3s;
            margin-bottom: 20px;
        }
        .btn-add-khach:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        .btn-add-khach:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .khach-counter {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 1.1em;
            font-weight: 600;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .form-actions button {
            flex: 1;
            padding: 15px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <?php include 'includes/header.php'; ?>
        
        <main class="main-container">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="ph ph-warning"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="ph ph-pencil"></i> Sửa Phiếu Thuê #<?= $maPhieuThue ?>
                    </h2>
                    <a href="phieu-thue.php" class="btn btn-secondary">
                        <i class="ph ph-arrow-left"></i> Quay lại
                    </a>
                </div>

                <form method="POST" id="formSuaPhieuThue" style="max-width: 900px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="ph ph-bed"></i> Phòng:</label>
                            <select name="maPhong" required class="form-control">
                                <option value="<?= $phieuThue['MaPhong'] ?>" selected>
                                    Phòng <?= htmlspecialchars($phieuThue['SoPhong']) ?> - <?= htmlspecialchars($phieuThue['TenLoai']) ?> (Hiện tại)
                                </option>
                                <?php foreach ($phongsTrong as $p): ?>
                                <option value="<?= $p['MaPhong'] ?>">
                                    Phòng <?= htmlspecialchars($p['SoPhong']) ?> - <?= htmlspecialchars($p['TenLoai']) ?>
                                    (<?= number_format($p['DonGiaCoBan']) ?>đ/đêm)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="ph ph-calendar"></i> Ngày Bắt Đầu:</label>
                            <input type="date" name="ngayBatDau" required class="form-control"
                                   value="<?= $phieuThue['NgayBatDauThue'] ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="ph ph-moon"></i> Số Đêm (1-14):</label>
                        <input type="number" name="soDem" min="1" max="14" required class="form-control"
                               value="<?= $phieuThue['SoDem'] ?? 1 ?>">
                    </div>

                    <hr style="margin: 30px 0; border: 1px solid #e9ecef;">

                    <div class="khach-counter">
                        <i class="ph ph-users"></i>
                        <span id="currentCount"><?= count($khachHienTai) ?></span> / <?= $soKhachToiDa ?> khách
                    </div>

                    <input type="hidden" id="soKhach" name="soKhach" value="<?= count($khachHienTai) ?>">

                    <button type="button" id="btnAddKhach" class="btn-add-khach">
                        <i class="ph ph-plus-circle"></i> Thêm Khách
                    </button>

                    <div id="khachContainer">
                        <?php foreach ($khachHienTai as $index => $k): ?>
                        <div class="khach-section" data-index="<?= $index + 1 ?>">
                            <button type="button" class="btn-remove-khach" onclick="removeKhach(this)" 
                                    <?= count($khachHienTai) <= 1 ? 'style="display:none;"' : '' ?>>
                                <i class="ph ph-x"></i>
                            </button>
                            <h4><i class="ph ph-user"></i> Khách <?= $index + 1 ?></h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tên Khách: <span style="color: red;">*</span></label>
                                    <input type="text" name="tenKhach<?= $index + 1 ?>" required class="form-control"
                                           value="<?= htmlspecialchars($k['TenKhach']) ?>" 
                                           placeholder="Nhập tên đầy đủ">
                                </div>
                                <div class="form-group">
                                    <label>Loại Khách: <span style="color: red;">*</span></label>
                                    <select name="loaiKhach<?= $index + 1 ?>" required class="form-control">
                                        <option value="Nội địa" <?= $k['LoaiKhach'] === 'Nội địa' ? 'selected' : '' ?>>🇻🇳 Nội địa</option>
                                        <option value="Nước ngoài" <?= $k['LoaiKhach'] === 'Nước ngoài' ? 'selected' : '' ?>>🌍 Nước ngoài</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>CMND/CCCD/Passport: <span style="color: red;">*</span></label>
                                    <input type="text" name="cmnd<?= $index + 1 ?>" required class="form-control"
                                           value="<?= htmlspecialchars($k['CMND']) ?>"
                                           placeholder="Số giấy tờ tùy thân">
                                </div>
                                <div class="form-group">
                                    <label>Địa Chỉ:</label>
                                    <input type="text" name="diaChi<?= $index + 1 ?>" class="form-control"
                                           value="<?= htmlspecialchars($k['DiaChi'] ?? '') ?>"
                                           placeholder="Địa chỉ liên hệ (không bắt buộc)">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <a href="phieu-thue.php" class="btn btn-secondary">
                            <i class="ph ph-x-circle"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const maxKhach = <?= $soKhachToiDa ?>;
        const khachContainer = document.getElementById('khachContainer');
        const btnAddKhach = document.getElementById('btnAddKhach');
        const soKhachInput = document.getElementById('soKhach');
        const currentCountSpan = document.getElementById('currentCount');
        let khachCount = <?= count($khachHienTai) ?>;

        btnAddKhach.addEventListener('click', function() {
            if (khachCount < maxKhach) {
                khachCount++;
                addKhachSection(khachCount);
                updateUI();
            }
        });

        function addKhachSection(index) {
            const section = document.createElement('div');
            section.className = 'khach-section';
            section.setAttribute('data-index', index);
            section.innerHTML = `
                <button type="button" class="btn-remove-khach" onclick="removeKhach(this)">
                    <i class="ph ph-x"></i>
                </button>
                <h4><i class="ph ph-user"></i> Khách ${index}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tên Khách: <span style="color: red;">*</span></label>
                        <input type="text" name="tenKhach${index}" required class="form-control"
                               placeholder="Nhập tên đầy đủ">
                    </div>
                    <div class="form-group">
                        <label>Loại Khách: <span style="color: red;">*</span></label>
                        <select name="loaiKhach${index}" required class="form-control">
                            <option value="Nội địa">🇻🇳 Nội địa</option>
                            <option value="Nước ngoài">🌍 Nước ngoài</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>CMND/CCCD/Passport: <span style="color: red;">*</span></label>
                        <input type="text" name="cmnd${index}" required class="form-control"
                               placeholder="Số giấy tờ tùy thân">
                    </div>
                    <div class="form-group">
                        <label>Địa Chỉ:</label>
                        <input type="text" name="diaChi${index}" class="form-control"
                               placeholder="Địa chỉ liên hệ (không bắt buộc)">
                    </div>
                </div>
            `;
            khachContainer.appendChild(section);
        }

        function removeKhach(button) {
            if (khachCount <= 1) {
                alert('Phải có ít nhất 1 khách!');
                return;
            }
            
            const section = button.closest('.khach-section');
            section.remove();
            khachCount--;
            
            // Cập nhật lại số thứ tự
            const sections = khachContainer.querySelectorAll('.khach-section');
            sections.forEach((sec, idx) => {
                const newIndex = idx + 1;
                sec.setAttribute('data-index', newIndex);
                sec.querySelector('h4').innerHTML = `<i class="ph ph-user"></i> Khách ${newIndex}`;
                
                // Cập nhật tên các input
                const inputs = sec.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.name.replace(/\d+$/, newIndex);
                    input.name = name;
                });
            });
            
            updateUI();
        }

        function updateUI() {
            soKhachInput.value = khachCount;
            currentCountSpan.textContent = khachCount;
            
            // Disable nút thêm nếu đạt max
            btnAddKhach.disabled = khachCount >= maxKhach;
            
            // Hiện/ẩn nút xóa
            const removeButtons = document.querySelectorAll('.btn-remove-khach');
            removeButtons.forEach(btn => {
                btn.style.display = khachCount <= 1 ? 'none' : 'flex';
            });
        }

        // Khởi tạo UI
        updateUI();
    </script>
</body>
</html>
