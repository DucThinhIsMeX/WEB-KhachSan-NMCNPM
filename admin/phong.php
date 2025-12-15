<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhongController.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$controller = new PhongController();
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Khởi tạo biến filter để tránh lỗi undefined
$loaiFilter = isset($_GET['loai']) ? $_GET['loai'] : null;
$tinhTrangFilter = isset($_GET['tinhtrang']) ? $_GET['tinhtrang'] : null;

// Xử lý thêm phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        try {
            $controller->themPhong(
                $_POST['soPhong'],
                $_POST['maLoaiPhong'],
                $_POST['ghiChu'] ?? null
            );
            $message = "✅ Thêm phòng thành công!";
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'update') {
        try {
            $controller->capNhatPhong(
                $_POST['maPhong'],
                $_POST['soPhong'],
                $_POST['maLoaiPhong'],
                $_POST['ghiChu'] ?? null
            );
            $message = "✅ Cập nhật phòng thành công!";
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete') {
        try {
            $controller->xoaPhong($_POST['maPhong']);
            $message = "✅ Xóa phòng thành công!";
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách phòng với filter
$phongs = $controller->traCuuPhong($loaiFilter, $tinhTrangFilter);
$loaiPhongs = $db->query("SELECT * FROM LOAIPHONG ORDER BY TenLoai")->fetchAll();
$page_title = 'Quản lý Phòng';
$phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <main class="main-container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="ph ph-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="ph ph-bed"></i> Danh Sách Phòng
                    </h2>
                    <button onclick="openModal()" class="btn btn-primary">
                        <i class="ph ph-plus-circle"></i> Thêm Phòng
                    </button>
                </div>
                
                <div class="filter-bar" style="margin-bottom: 20px;">
                    <form method="GET" style="display: flex; gap: 15px; align-items: center;">
                        <div class="form-group" style="margin: 0; flex: 1;">
                            <label><i class="ph ph-funnel"></i> Loại Phòng:</label>
                            <select name="loai" onchange="this.form.submit()" class="form-control">
                                <option value="">Tất cả</option>
                                <?php foreach ($loaiPhongs as $loai): ?>
                                <option value="<?= $loai['MaLoaiPhong'] ?>" <?= $loaiFilter == $loai['MaLoaiPhong'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loai['TenLoai']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0; flex: 1;">
                            <label><i class="ph ph-check-square"></i> Trạng Thái:</label>
                            <select name="tinhtrang" onchange="this.form.submit()" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="Trống" <?= $tinhTrangFilter == 'Trống' ? 'selected' : '' ?>>Trống</option>
                                <option value="Đã thuê" <?= $tinhTrangFilter == 'Đã thuê' ? 'selected' : '' ?>>Đã thuê</option>
                            </select>
                        </div>
                    </form>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Số Phòng</th>
                            <th>Loại</th>
                            <th>Đơn Giá</th>
                            <th>Trạng Thái</th>
                            <th>Ghi Chú</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($phongs as $phong): ?>
                        <tr>
                            <td><strong>#<?= $phong['MaPhong'] ?></strong></td>
                            <td><strong><?= htmlspecialchars($phong['SoPhong']) ?></strong></td>
                            <td><?= htmlspecialchars($phong['TenLoai']) ?></td>
                            <td><strong><?= number_format($phong['DonGiaCoBan']) ?>đ</strong></td>
                            <td>
                                <span class="status-badge <?= $phong['TinhTrang'] === 'Trống' ? 'available' : 'occupied' ?>">
                                    <i class="ph ph-<?= $phong['TinhTrang'] === 'Trống' ? 'check-circle' : 'lock-key' ?>"></i>
                                    <?= htmlspecialchars($phong['TinhTrang']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($phong['GhiChu'] ?? '-') ?></td>
                            <td>
                                <button onclick='editPhong(<?= json_encode($phong) ?>)' class="btn btn-sm btn-primary" title="Sửa">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <?php if ($phong['TinhTrang'] === 'Trống'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="maPhong" value="<?= $phong['MaPhong'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Xác nhận xóa phòng?')" title="Xóa">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Thêm/Sửa Phòng -->
    <div id="phongModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="ph ph-plus-circle"></i> Thêm Phòng Mới</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" id="phongForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="maPhong" id="maPhong">
                
                <div class="form-group">
                    <label><i class="ph ph-number-square"></i> Số Phòng *</label>
                    <input type="text" name="soPhong" id="soPhong" required class="form-control">
                </div>

                <div class="form-group">
                    <label><i class="ph ph-tag"></i> Loại Phòng *</label>
                    <select name="maLoaiPhong" id="maLoaiPhong" required class="form-control">
                        <?php foreach ($loaiPhongs as $loai): ?>
                        <option value="<?= $loai['MaLoaiPhong'] ?>">
                            <?= htmlspecialchars($loai['TenLoai']) ?> - <?= number_format($loai['DonGiaCoBan']) ?>đ
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="ph ph-note-pencil"></i> Ghi Chú</label>
                    <textarea name="ghiChu" id="ghiChu" rows="3" class="form-control"></textarea>
                </div>

                <div class="btn-group">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        <i class="ph ph-x"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('phongModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="ph ph-plus-circle"></i> Thêm Phòng Mới';
            document.getElementById('formAction').value = 'create';
            document.getElementById('phongForm').reset();
        }

        function closeModal() {
            document.getElementById('phongModal').style.display = 'none';
        }

        function editPhong(phong) {
            document.getElementById('phongModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Sửa Phòng';
            document.getElementById('formAction').value = 'update';
            document.getElementById('maPhong').value = phong.MaPhong;
            document.getElementById('soPhong').value = phong.SoPhong;
            document.getElementById('maLoaiPhong').value = phong.MaLoaiPhong;
            document.getElementById('ghiChu').value = phong.GhiChu || '';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('phongModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
