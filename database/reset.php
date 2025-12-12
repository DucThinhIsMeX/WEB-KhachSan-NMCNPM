<?php
$db_file = __DIR__ . '/hotel.db';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Reset Database</title>
</head>
<body style='font-family: Arial; padding: 40px; background: #f5f5f5;'>
<div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>";

if (isset($_POST['confirm']) && $_POST['confirm'] == 'YES') {
    if (file_exists($db_file)) {
        unlink($db_file);
        echo "<h2 style='color: green;'>✓ Đã xóa database cũ</h2>";
    }
    
    echo "<h3>Đang tạo database mới...</h3>";
    include 'init.php';
    
    echo "<p><a href='../test_database.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>Kiểm tra Database</a></p>";
    
} else {
    echo "<h2>⚠️ Xác nhận Reset Database</h2>";
    echo "<p style='color: red;'><strong>Cảnh báo:</strong> Hành động này sẽ XÓA toàn bộ dữ liệu hiện tại!</p>";
    echo "<form method='POST'>
            <p>Gõ <strong>YES</strong> để xác nhận:</p>
            <input type='text' name='confirm' style='padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>
            <button type='submit' style='padding: 10px 30px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>Reset Database</button>
            <a href='../index.php' style='padding: 10px 30px; background: #999; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-left: 10px;'>Hủy</a>
          </form>";
}

echo "</div></body></html>";
?>
