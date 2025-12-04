<?php require_once __DIR__ . '/../controllers/PhongController.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý Khách sạn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-badge {
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-left: 10px;
        }
        .customer-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: 0.3s;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .customer-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <!-- Nút chuyển sang trang khách -->
    <a href="http://localhost:5500" class="customer-link" target="_blank">
        <span>🌐</span>
        <span>Trang khách hàng</span>
    </a>

    <div class="container">
        <header>
            <h1>🏨 Admin - Hệ thống Quản lý Khách sạn <span class="admin-badge">ADMIN</span></h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="../pages/phong.php">Quản lý Phòng</a>
                <a href="../pages/khachhang.php">Khách hàng</a>
                <a href="../pages/phieuthue.php">Phiếu thuê</a>
                <a href="../pages/hoadon.php">Hóa đơn</a>
                <a href="../pages/baocao.php">Báo cáo</a>
                <a href="../pages/thamso.php">Tham số</a>
            </nav>
        </header>

        <main>
            <section class="dashboard">
                <h2>Dashboard Quản trị</h2>
                <?php
                $controller = new PhongController();
                $phongTrong = count($controller->traCuuPhong(null, 'Trống'));
                $phongDaThue = count($controller->traCuuPhong(null, 'Đã thuê'));
                $tongPhong = count($controller->getAllPhong());
                ?>
                <div class="stats">
                    <div class="stat-card">
                        <h3><?= $tongPhong ?></h3>
                        <p>Tổng số phòng</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $phongTrong ?></h3>
                        <p>Phòng trống</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $phongDaThue ?></h3>
                        <p>Phòng đã thuê</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $tongPhong > 0 ? round(($phongDaThue/$tongPhong)*100) : 0 ?>%</h3>
                        <p>Tỷ lệ lấp đầy</p>
                    </div>
                </div>
            </section>

            <section class="recent-rooms">
                <h2>Danh sách Phòng</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Số phòng</th>
                            <th>Loại phòng</th>
                            <th>Đơn giá</th>
                            <th>Tình trạng</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $phongs = $controller->getAllPhong();
                        foreach ($phongs as $phong):
                        ?>
                        <tr>
                            <td><?= $phong['MaPhong'] ?></td>
                            <td><?= $phong['SoPhong'] ?></td>
                            <td><?= $phong['TenLoai'] ?></td>
                            <td><?= number_format($phong['DonGiaCoBan']) ?>đ</td>
                            <td><span class="status-<?= strtolower(str_replace(' ', '-', $phong['TinhTrang'])) ?>"><?= $phong['TinhTrang'] ?></span></td>
                            <td><?= $phong['GhiChu'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Hệ thống Quản lý Khách sạn - Admin Panel (Port 8000)</p>
        </footer>
    </div>
</body>
</html>
