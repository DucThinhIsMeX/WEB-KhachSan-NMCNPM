<!-- Header -->
<header class="admin-header">
    <h1 class="header-title"><?= $page_title ?? 'Admin Panel' ?></h1>
    <div class="header-actions">
        <button class="btn-icon" title="Thông báo">
            <i class="ph ph-bell"></i>
        </button>
        <div class="user-menu">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['fullname'] ?? 'A', 0, 1)) ?></div>
            <span><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
        </div>
    </div>
</header>
