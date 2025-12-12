<?php 
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PhongController.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$controller = new PhongController();
$phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
$page_title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quản lý Khách sạn</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <!-- Main Container -->
        <main class="main-container">
            <?php
            $phongTrong = count($controller->traCuuPhong(null, 'Trống'));
            $phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
            $tongPhong = count($controller->getAllPhong());
            $tyLeLapDay = $tongPhong > 0 ? round(($phongDaThue/$tongPhong)*100) : 0;
            ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?= $tongPhong ?></div>
                            <div class="stat-label">Tổng Số Phòng</div>
                        </div>
                        <div class="stat-icon">🏨</div>
                    </div>
                    <div class="stat-change up">
                        <span>↗</span> Hoạt động bình thường
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?= $phongTrong ?></div>
                            <div class="stat-label">Phòng Trống</div>
                        </div>
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-change">
                        Sẵn sàng cho thuê
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?= $phongDaThue ?></div>
                            <div class="stat-label">Phòng Đã Thuê</div>
                        </div>
                        <div class="stat-icon">🔒</div>
                    </div>
                    <div class="stat-change">
                        Đang hoạt động
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?= $tyLeLapDay ?>%</div>
                            <div class="stat-label">Tỷ Lệ Lấp Đầy</div>
                        </div>
                        <div class="stat-icon">📊</div>
                    </div>
                    <div class="stat-change <?= $tyLeLapDay >= 70 ? 'up' : 'down' ?>">
                        <span><?= $tyLeLapDay >= 70 ? '↗' : '↘' ?></span> 
                        <?= $tyLeLapDay >= 70 ? 'Tốt' : 'Cần cải thiện' ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">⚡ Thao Tác Nhanh</h2>
                </div>
                <div class="quick-actions">
                    <a href="phong.php?action=add" class="action-card">
                        <div class="icon">➕</div>
                        <h3>Thêm Phòng</h3>
                        <p>Lập danh mục phòng mới</p>
                    </a>
                    <a href="phieu-thue.php?action=add" class="action-card">
                        <div class="icon">📋</div>
                        <h3>Tạo Phiếu Thuê</h3>
                        <p>Cho thuê phòng cho khách</p>
                    </a>
                    <a href="hoa-don.php?action=add" class="action-card">
                        <div class="icon">💳</div>
                        <h3>Lập Hóa Đơn</h3>
                        <p>Thanh toán cho khách</p>
                    </a>
                    <a href="bao-cao.php" class="action-card">
                        <div class="icon">📈</div>
                        <h3>Xem Báo Cáo</h3>
                        <p>Báo cáo doanh thu tháng</p>
                    </a>
                </div>
            </div>

            <!-- Recent Rooms -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        🛏️ Danh Sách Phòng
                    </h2>
                    <a href="phong.php" class="btn btn-primary">Xem Tất Cả →</a>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $phongs = $controller->getAllPhong();
                        foreach (array_slice($phongs, 0, 10) as $phong):
                        ?>
                        <tr>
                            <td><strong>#<?= $phong['MaPhong'] ?></strong></td>
                            <td><strong><?= $phong['SoPhong'] ?></strong></td>
                            <td><?= $phong['TenLoai'] ?></td>
                            <td><strong><?= number_format($phong['DonGiaCoBan']) ?>đ</strong></td>
                            <td>
                                <span class="status-badge <?= $phong['TinhTrang'] === 'Trống' ? 'available' : 'occupied' ?>">
                                    <?= $phong['TinhTrang'] ?>
                                </span>
                            </td>
                            <td><?= $phong['GhiChu'] ?? '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        document.querySelector('.btn-icon')?.addEventListener('click', () => {
            document.querySelector('.admin-sidebar')?.classList.toggle('active');
        });
    </script>
</body>
</html>