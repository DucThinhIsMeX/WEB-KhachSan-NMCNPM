<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAdmin();

$message = '';
$error = '';

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $result = $auth->createUser(
            $_POST['username'],
            $_POST['password'],
            $_POST['fullname'],
            $_POST['role']
        );
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    elseif ($action === 'update') {
        $result = $auth->updateUser(
            $_POST['user_id'],
            $_POST['fullname'],
            $_POST['role'],
            $_POST['status']
        );
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    elseif ($action === 'change_password') {
        $result = $auth->changePassword(
            $_POST['user_id'],
            $_POST['new_password']
        );
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    elseif ($action === 'delete') {
        $result = $auth->deleteUser($_POST['user_id']);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$users = $auth->getAllUsers();
$page_title = 'Quản Lý Tài Khoản';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Tài Khoản</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 50px auto; padding: 30px; width: 90%; max-width: 500px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e0e0e0; }
        .close { font-size: 28px; font-weight: bold; cursor: pointer; color: #999; }
        .close:hover { color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 8px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <?php 
    $phongDaThue = 0;
    include 'includes/sidebar.php'; 
    ?>
    
    <div class="admin-content">
        <?php include 'includes/header.php'; ?>
        
        <main class="main-container">
            <?php if ($message): ?>
            <div class="alert alert-success"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><i class="ph ph-warning"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title"><i class="ph ph-users"></i> Quản Lý Tài Khoản</h2>
                    <button onclick="openModal('createModal')" class="btn btn-primary">
                        <i class="ph ph-plus-circle"></i> Thêm Tài Khoản
                    </button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Đăng Nhập</th>
                            <th>Họ Tên</th>
                            <th>Vai Trò</th>
                            <th>Trạng Thái</th>
                            <th>Ngày Tạo</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?= $user['MaNguoiDung'] ?></td>
                            <td><strong><?= htmlspecialchars($user['TenDangNhap']) ?></strong></td>
                            <td><?= htmlspecialchars($user['HoTen']) ?></td>
                            <td><span class="badge <?= $user['VaiTro'] === 'Admin' ? 'badge-primary' : 'badge-secondary' ?>"><?= $user['VaiTro'] ?></span></td>
                            <td><span class="status-badge <?= $user['TrangThai'] === 'Hoạt động' ? 'available' : 'occupied' ?>"><?= $user['TrangThai'] ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($user['NgayTao'])) ?></td>
                            <td>
                                <button onclick='editUser(<?= json_encode($user) ?>)' class="btn btn-sm btn-primary" title="Sửa">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button onclick='changePassword(<?= $user['MaNguoiDung'] ?>, "<?= htmlspecialchars($user['TenDangNhap']) ?>")' class="btn btn-sm btn-warning" title="Đổi mật khẩu">
                                    <i class="ph ph-lock-key"></i>
                                </button>
                                <?php if ($user['MaNguoiDung'] != $_SESSION['user_id']): ?>
                                <button onclick='deleteUser(<?= $user['MaNguoiDung'] ?>, "<?= htmlspecialchars($user['TenDangNhap']) ?>")' class="btn btn-sm btn-danger" title="Xóa">
                                    <i class="ph ph-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal Thêm Tài Khoản -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="ph ph-user-plus"></i> Thêm Tài Khoản Mới</h2>
                <span class="close" onclick="closeModal('createModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label><i class="ph ph-user"></i> Tên Đăng Nhập *</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label><i class="ph ph-lock-key"></i> Mật Khẩu *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label><i class="ph ph-identification-card"></i> Họ Tên *</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="form-group">
                    <label><i class="ph ph-user-circle"></i> Vai Trò *</label>
                    <select name="role" required>
                        <option value="NhanVien">Nhân Viên</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="button" onclick="closeModal('createModal')" class="btn btn-secondary">
                        <i class="ph ph-x"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-check-circle"></i> Tạo Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Sửa Tài Khoản -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="ph ph-pencil-simple"></i> Sửa Tài Khoản</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>Tên Đăng Nhập</label>
                    <input type="text" id="edit_username" disabled>
                </div>
                <div class="form-group">
                    <label>Họ Tên *</label>
                    <input type="text" name="fullname" id="edit_fullname" required>
                </div>
                <div class="form-group">
                    <label>Vai Trò *</label>
                    <select name="role" id="edit_role" required>
                        <option value="NhanVien">Nhân Viên</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng Thái *</label>
                    <select name="status" id="edit_status" required>
                        <option value="Hoạt động">Hoạt động</option>
                        <option value="Khóa">Khóa</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary">
                        <i class="ph ph-x"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Đổi Mật Khẩu -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="ph ph-lock-key"></i> Đổi Mật Khẩu</h2>
                <span class="close" onclick="closeModal('passwordModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="user_id" id="pwd_user_id">
                <div class="form-group">
                    <label>Tên Đăng Nhập</label>
                    <input type="text" id="pwd_username" disabled>
                </div>
                <div class="form-group">
                    <label>Mật Khẩu Mới *</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="btn-group">
                    <button type="button" onclick="closeModal('passwordModal')" class="btn btn-secondary">
                        <i class="ph ph-x"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-key"></i> Đổi Mật Khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.MaNguoiDung;
            document.getElementById('edit_username').value = user.TenDangNhap;
            document.getElementById('edit_fullname').value = user.HoTen;
            document.getElementById('edit_role').value = user.VaiTro;
            document.getElementById('edit_status').value = user.TrangThai;
            openModal('editModal');
        }
        
        function changePassword(id, username) {
            document.getElementById('pwd_user_id').value = id;
            document.getElementById('pwd_username').value = username;
            openModal('passwordModal');
        }
        
        function deleteUser(id, username) {
            if (confirm(`Bạn có chắc muốn xóa tài khoản "${username}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
