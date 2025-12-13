<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/BaoCaoController.php';
require_once __DIR__ . '/../controllers/PhongController.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$baoCaoCtrl = new BaoCaoController();
$controller = new PhongController();

$message = '';
$error = '';
$baoCao = null;

// Xử lý lập báo cáo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $maBaoCao = $baoCaoCtrl->lapBaoCao($_POST['thang'], $_POST['nam']);
        $baoCao = $baoCaoCtrl->xemBaoCao($maBaoCao);
        $message = "Lập báo cáo thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
} elseif (isset($_GET['thang']) && isset($_GET['nam'])) {
    try {
        $maBaoCao = $baoCaoCtrl->lapBaoCao($_GET['thang'], $_GET['nam']);
        $baoCao = $baoCaoCtrl->xemBaoCao($maBaoCao);
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

$tongDoanhThu = 0;
if ($baoCao) {
    foreach ($baoCao as $item) {
        $tongDoanhThu += $item['DoanhThu'];
    }
}

$page_title = 'Báo Cáo Doanh Thu';
$phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Doanh Thu</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <main class="main-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">📅 Chọn Tháng Báo Cáo</h2>
                </div>
                <form method="POST" style="max-width: 800px;">
                    <div class="form-group">
                        <label>Tháng:</label>
                        <select name="thang" required class="form-control">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                    Tháng <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Năm:</label>
                        <select name="nam" required class="form-control">
                            <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">📊 Xem Báo Cáo</button>
                </form>
            </div>

            <?php if ($baoCao): ?>
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">📈 Kết Quả Báo Cáo Tháng <?= $baoCao[0]['Thang'] ?>/<?= $baoCao[0]['Nam'] ?></h2>
                </div>
                
                <div class="stats-grid" style="margin-bottom: 30px;">
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?= number_format($tongDoanhThu) ?>đ</div>
                                <div class="stat-label">Tổng Doanh Thu</div>
                            </div>
                            <div class="stat-icon">💰</div>
                        </div>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Loại Phòng</th>
                            <th>Doanh Thu</th>
                            <th>Tỷ Lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baoCao as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['TenLoai']) ?></strong></td>
                            <td><?= number_format($item['DoanhThu']) ?>đ</td>
                            <td><?= number_format($item['TyLe'], 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 10px;">
                    <canvas id="chartDoanhThu" height="100"></canvas>
                </div>
                
                <script>
                const ctx = document.getElementById('chartDoanhThu').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [<?= implode(',', array_map(fn($i) => "'{$i['TenLoai']}'", $baoCao)) ?>],
                        datasets: [{
                            label: 'Doanh Thu (VNĐ)',
                            data: [<?= implode(',', array_column($baoCao, 'DoanhThu')) ?>],
                            backgroundColor: ['#667eea', '#764ba2', '#f093fb']
                        }]
                    },
                    options: { 
                        responsive: true,
                        maintainAspectRatio: true
                    }
                });
                </script>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
