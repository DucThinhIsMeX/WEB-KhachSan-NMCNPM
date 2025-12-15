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

// Khởi tạo các biến để tránh lỗi undefined
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

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
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
                    <h2 class="section-title">
                        <i class="ph ph-chart-line-up"></i> Báo Cáo Doanh Thu Tháng
                    </h2>
                </div>
                
                <form method="GET" style="max-width: 600px; margin-bottom: 30px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px;">
                        <div class="form-group">
                            <label><i class="ph ph-calendar"></i> Tháng:</label>
                            <select name="month" class="form-control">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= $month == $i ? 'selected' : '' ?>>
                                    Tháng <?= $i ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="ph ph-calendar-blank"></i> Năm:</label>
                            <select name="year" class="form-control">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph ph-magnifying-glass"></i> Xem Báo Cáo
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($baoCao): ?>
                    <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px;">
                        <h3 style="margin-bottom: 20px;">
                            <i class="ph ph-chart-bar"></i> Tổng Quan Tháng <?= $month ?>/<?= $year ?>
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px;">
                                <div style="font-size: 0.9em; opacity: 0.9;">Tổng Doanh Thu</div>
                                <div style="font-size: 2em; font-weight: bold; margin-top: 10px;">
                                    <i class="ph ph-currency-circle-dollar"></i>
                                    <?= number_format($baoCao['TongDoanhThu']) ?>đ
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <div>
                            <h3><i class="ph ph-table"></i> Doanh Thu Theo Loại Phòng</h3>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Loại Phòng</th>
                                        <th>Doanh Thu</th>
                                        <th>Tỷ Lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chiTiet as $ct): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ct['TenLoai']) ?></strong></td>
                                        <td><strong><?= number_format($ct['DoanhThu']) ?>đ</strong></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="flex: 1; background: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden;">
                                                    <div style="width: <?= $ct['TyLe'] ?>%; background: linear-gradient(90deg, #667eea, #764ba2); height: 100%;"></div>
                                                </div>
                                                <strong><?= number_format($ct['TyLe'], 1) ?>%</strong>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <h3><i class="ph ph-chart-pie"></i> Biểu Đồ Doanh Thu</h3>
                            <canvas id="revenueChart" width="400" height="300"></canvas>
                        </div>
                    </div>

                    <script>
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        const chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($chiTiet, 'TenLoai')) ?>,
                                datasets: [{
                                    label: 'Doanh Thu (VNĐ)',
                                    data: <?= json_encode(array_column($chiTiet, 'DoanhThu')) ?>,
                                    backgroundColor: [
                                        'rgba(102, 126, 234, 0.8)',
                                        'rgba(118, 75, 162, 0.8)',
                                        'rgba(240, 147, 251, 0.8)'
                                    ],
                                    borderColor: [
                                        'rgba(102, 126, 234, 1)',
                                        'rgba(118, 75, 162, 1)',
                                        'rgba(240, 147, 251, 1)'
                                    ],
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Doanh Thu Theo Loại Phòng'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString('vi-VN') + 'đ';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="ph ph-chart-line-down" style="font-size: 4em; color: #999; margin-bottom: 20px;"></i>
                        <h3>Chưa Có Dữ Liệu</h3>
                        <p>Chọn tháng/năm để xem báo cáo doanh thu</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
