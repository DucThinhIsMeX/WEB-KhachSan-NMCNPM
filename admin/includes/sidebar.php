<?php
// Lấy tên file hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h1>
            <i class="ph-fill ph-buildings"></i> Hotel MS
            <span class="badge">ADMIN</span>
        </h1>
        <div class="user-info" style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="user-avatar" style="width: 35px; height: 35px; background: #fff; color: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    <?= strtoupper(substr($_SESSION['fullname'] ?? 'A', 0, 1)) ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="color: #fff; font-weight: 500; font-size: 0.9em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
                    </div>
                    <div style="color: rgba(255,255,255,0.7); font-size: 0.75em;">
                        <?= htmlspecialchars($_SESSION['role'] ?? 'Admin') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-chart-bar icon"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Quản Lý</div>
            <a href="phong.php" class="nav-link <?= $current_page === 'phong.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-bed icon"></i>
                <span>Quản lý Phòng</span>
            </a>
            <a href="phieu-thue.php" class="nav-link <?= $current_page === 'phieu-thue.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-ticket icon"></i>
                <span>Phiếu Thuê</span>
                <?php
                if (isset($phongDaThue) && $phongDaThue > 0):
                ?>
                <span class="badge"><?= $phongDaThue ?></span>
                <?php endif; ?>
            </a>
            <a href="hoa-don.php" class="nav-link <?= $current_page === 'hoa-don.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-currency-circle-dollar icon"></i>
                <span>Hóa Đơn</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Báo Cáo</div>
            <a href="bao-cao.php" class="nav-link <?= $current_page === 'bao-cao.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-chart-line-up icon"></i>
                <span>Báo Cáo Doanh Thu</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Cài Đặt</div>
            <a href="tham-so.php" class="nav-link <?= $current_page === 'tham-so.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-gear icon"></i>
                <span>Tham Số Hệ Thống</span>
            </a>
            <a href="tai-khoan.php" class="nav-link <?= $current_page === 'tai-khoan.php' ? 'active' : '' ?>">
                <i class="ph-fill ph-user icon"></i>
                <span>Quản Lý Tài Khoản</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Khác</div>
            <a href="../index.php" class="nav-link">
                <i class="ph-fill ph-house icon"></i>
                <span>Trang Đặt Phòng</span>
            </a>
            <a href="../test_database.php" class="nav-link">
                <i class="ph-fill ph-wrench icon"></i>
                <span>Kiểm Tra Database</span>
            </a>
            <a href="logout.php" class="nav-link" style="color: #f44336;">
                <i class="ph-fill ph-sign-out icon"></i>
                <span>Đăng Xuất</span>
            </a>
        </div>
    </nav>
</aside>
