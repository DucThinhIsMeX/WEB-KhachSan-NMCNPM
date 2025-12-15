<?php
/**
 * Component chuyển đổi giữa đăng nhập khách hàng và admin
 * 
 * @param string $currentType - 'customer' hoặc 'admin'
 */
function renderLoginSwitcher($currentType = 'customer') {
    if ($currentType === 'customer') {
        // Hiển thị link tới admin login
        ?>
        <div class="admin-login-section">
            <h3>Bạn là quản trị viên?</h3>
            <a href="../admin/login.php" class="btn-admin-login">
                <i class="ph-fill ph-shield-check"></i>
                <span>Đăng nhập quản trị</span>
            </a>
        </div>
        <?php
    } else {
        // Hiển thị link tới customer login
        ?>
        <div class="customer-login-link">
            <p>Bạn là khách hàng?</p>
            <a href="../customer/login.php">
                <i class="ph ph-user-circle"></i>
                Đăng nhập khách hàng
            </a>
        </div>
        <?php
    }
}
?>
