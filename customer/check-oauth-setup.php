<?php
require_once __DIR__ . '/../config/oauth.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Kiểm Tra OAuth Setup</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h2 { color: #667eea; }
        .info-box { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🔍 Kiểm Tra Cấu Hình OAuth</h2>";

echo "<div class='info-box'>";
echo "<h3>📋 Thông tin trong Code:</h3>";
echo "<p><strong>Redirect URI:</strong><br>";
echo "<code>" . GOOGLE_REDIRECT_URI . "</code></p>";
echo "<p><strong>Client ID:</strong><br>";
echo "<code>" . GOOGLE_CLIENT_ID . "</code></p>";
echo "</div>";

echo "<div class='info-box'>";
echo "<h3>🌐 URL Authorization đầy đủ:</h3>";
$authUrl = getGoogleAuthUrl() . '&state=google';
echo "<textarea style='width:100%;height:100px;'>" . htmlspecialchars($authUrl) . "</textarea>";
echo "</div>";

echo "<div class='info-box'>";
echo "<h3>✅ Checklist Google Console:</h3>";
echo "<ol>";
echo "<li>Vào <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console</a></li>";
echo "<li>Chọn OAuth 2.0 Client ID của bạn</li>";
echo "<li>Trong <strong>Authorized redirect URIs</strong>, đảm bảo có URI này:</li>";
echo "<li><code>" . GOOGLE_REDIRECT_URI . "</code></li>";
echo "<li>Click <strong>Save</strong></li>";
echo "<li>Đợi 5-10 phút để thay đổi có hiệu lực</li>";
echo "</ol>";
echo "</div>";

// Kiểm tra file callback có tồn tại không
$callbackFile = __DIR__ . '/oauth-callback.php';
echo "<div class='info-box'>";
echo "<h3>📁 Kiểm tra file callback:</h3>";
if (file_exists($callbackFile)) {
    echo "<p class='success'>✅ File <code>oauth-callback.php</code> tồn tại</p>";
} else {
    echo "<p class='error'>❌ File <code>oauth-callback.php</code> KHÔNG tồn tại!</p>";
    echo "<p>Bạn cần tạo file này hoặc đổi tên từ <code>google-callback.php</code></p>";
}
echo "</div>";

// Kiểm tra database
try {
    require_once __DIR__ . '/../config/database.php';
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT COUNT(*) FROM KHACHHANG_USERS");
    $count = $stmt->fetchColumn();
    
    echo "<div class='info-box'>";
    echo "<h3>🗄️ Kiểm tra Database:</h3>";
    echo "<p class='success'>✅ Bảng KHACHHANG_USERS tồn tại</p>";
    echo "<p>Số lượng user: <strong>$count</strong></p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='info-box'>";
    echo "<h3>🗄️ Kiểm tra Database:</h3>";
    echo "<p class='error'>❌ Lỗi database: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Chạy <code>php database/init.php</code> để khởi tạo database</p>";
    echo "</div>";
}

echo "<div class='info-box'>";
echo "<h3>🧪 Test đăng nhập:</h3>";
echo "<a href='login.php' style='display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;'>Đi tới trang đăng nhập</a>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
