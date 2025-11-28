<?php require_once 'controllers/PhongController.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Khách sạn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏨 Hệ thống Quản lý Khách sạn</h1>
            <nav>
                <a href="index.php">Trang chủ</a>
                <a href="pages/phong.php">Quản lý Phòng</a>
                <a href="pages/khachhang.php">Khách hàng</a>
                <a href="pages/phieuthue.php">Phiếu thuê</a>
                <a href="pages/hoadon.php">Hóa đơn</a>
                <a href="pages/baocao.php">Báo cáo</a>
                <a href="pages/thamso.php">Tham số</a>
            </nav>
        </header>

        <main>
            <section class="dashboard">
                <h2>Dashboard</h2>
                <?php
                $controller = new PhongController();
                $phongTrong = count($controller->traCuuPhong(null, 'Trống'));
                $phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
                ?>
                <div class="stats">
                    <div class="stat-card">
                        <h3><?= $phongTrong ?></h3>
                        <p>Phòng trống</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $phongDaThue ?></h3>
                        <p>Phòng đã thuê</p>
                    </div>
                </div>
            </section>

            <section class="recent-rooms">
                <h2>Danh sách Phòng</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Số phòng</th>
                            <th>Loại phòng</th>
                            <th>Đơn giá</th>
                            <th>Tình trạng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $phongs = $controller->getAllPhong();
                        foreach ($phongs as $phong):
                        ?>
                        <tr>
                            <td><?= $phong['SoPhong'] ?></td>
                            <td><?= $phong['TenLoai'] ?></td>
                            <td><?= number_format($phong['DonGiaCoBan']) ?>đ</td>
                            <td><span class="status-<?= strtolower(str_replace(' ', '-', $phong['TinhTrang'])) ?>"><?= $phong['TinhTrang'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Hệ thống Quản lý Khách sạn - Nhập môn CNPM</p>
        </footer>
    </div>
</body>
</html>
