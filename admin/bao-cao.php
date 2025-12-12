<?php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/BaoCaoController.php';

// Kiểm tra đăng nhập
$auth = new AuthController();
$auth->requireAdmin();

$baoCaoCtrl = new BaoCaoController();

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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Doanh Thu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Báo Cáo Doanh Thu</h1>
            <nav>
                <a href="index.php">🏠 Dashboard</a>
                <a href="phong.php">🛏️ Quản lý Phòng</a>
                <a href="phieu-thue.php">📝 Phiếu Thuê</a>
                <a href="hoa-don.php">💰 Hóa Đơn</a>
                <a href="bao-cao.php" class="active">📊 Báo Cáo</a>
                <a href="tham-so.php">⚙️ Tham Số</a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <section>
                <h2>📅 Chọn Tháng Báo Cáo</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Tháng:</label>
                        <select name="thang" required>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                    Tháng <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Năm:</label>
                        <select name="nam" required>
                            <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">📊 Xem Báo Cáo</button>
                </form>
            </section>

            <?php if ($baoCao): ?>
            <section>
                <h2>📈 Kết Quả Báo Cáo Tháng <?= $baoCao[0]['Thang'] ?>/<?= $baoCao[0]['Nam'] ?></h2>
                
                <div class="stats">
                    <div class="stat-card">
                        <h3><?= number_format($tongDoanhThu) ?>đ</h3>
                        <p>Tổng Doanh Thu</p>
                    </div>
                </div>

                <table>
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
                            <td><strong><?= $item['TenLoai'] ?></strong></td>
                            <td><?= number_format($item['DoanhThu']) ?>đ</td>
                            <td><?= number_format($item['TyLe'], 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <canvas id="chartDoanhThu" width="400" height="200"></canvas>
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
                    options: { responsive: true }
                });
                </script>
            </section>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2024 Hệ thống Quản lý Khách sạn</p>
        </footer>
    </div>
</body>
</html>
