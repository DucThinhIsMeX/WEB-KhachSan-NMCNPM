<?php
require_once __DIR__ . '/../config/oauth.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Kiểm Tra OAuth Credentials</title>
    <style>
        body { font-family: Arial; padding: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        h2 { color: #667eea; margin-bottom: 20px; }
        .info-box { background: #f8f9fa; padding: 20px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 8px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        code { background: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 0.9em; word-break: break-all; }
        .check-item { padding: 12px; margin: 8px 0; background: white; border-radius: 6px; display: flex; align-items: start; gap: 10px; }
        .check-icon { font-size: 1.5em; flex-shrink: 0; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: bold; }
        .btn:hover { opacity: 0.9; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 0.9em; }
        .step { background: linear-gradient(135deg, #e0e7ff 0%, #e9d5ff 100%); padding: 15px; margin: 15px 0; border-radius: 8px; }
        .step strong { color: #667eea; }
    </style>
</head>
<body>
<div class='container'>
    <h2>🔐 Kiểm Tra OAuth Credentials</h2>";

// Kiểm tra Client ID
echo "<div class='info-box'>";
echo "<h3>1️⃣ Client ID</h3>";
$clientId = GOOGLE_CLIENT_ID;
echo "<div class='check-item'>";

if (empty($clientId) || $clientId === 'YOUR_GOOGLE_CLIENT_ID') {
    echo "<span class='check-icon error'>❌</span>";
    echo "<div>";
    echo "<p class='error'>Client ID chưa được cấu hình!</p>";
    echo "<p>Bạn cần thay thế <code>YOUR_GOOGLE_CLIENT_ID</code> bằng Client ID thực từ Google Console.</p>";
    echo "</div>";
} else {
    echo "<span class='check-icon success'>✅</span>";
    echo "<div>";
    echo "<p class='success'>Client ID đã được cấu hình</p>";
    echo "<p><strong>Length:</strong> " . strlen($clientId) . " ký tự</p>";
    echo "<p><strong>Starts with:</strong> <code>" . substr($clientId, 0, 20) . "...</code></p>";
    echo "<p><strong>Ends with:</strong> <code>..." . substr($clientId, -20) . "</code></p>";
    
    // Check định dạng Client ID
    if (strpos($clientId, '.apps.googleusercontent.com') !== false) {
        echo "<p class='success'>✓ Định dạng hợp lệ (.apps.googleusercontent.com)</p>";
    } else {
        echo "<p class='warning'>⚠️ Client ID thường kết thúc bằng .apps.googleusercontent.com</p>";
    }
}
echo "</div></div>";
echo "</div>";

// Kiểm tra Client Secret
echo "<div class='info-box'>";
echo "<h3>2️⃣ Client Secret</h3>";
$clientSecret = GOOGLE_CLIENT_SECRET;
echo "<div class='check-item'>";

if (empty($clientSecret) || $clientSecret === 'YOUR_GOOGLE_CLIENT_SECRET') {
    echo "<span class='check-icon error'>❌</span>";
    echo "<div>";
    echo "<p class='error'>Client Secret chưa được cấu hình!</p>";
    echo "<p>Bạn cần thay thế <code>YOUR_GOOGLE_CLIENT_SECRET</code> bằng Client Secret thực từ Google Console.</p>";
    echo "</div>";
} else {
    echo "<span class='check-icon success'>✅</span>";
    echo "<div>";
    echo "<p class='success'>Client Secret đã được cấu hình</p>";
    echo "<p><strong>Length:</strong> " . strlen($clientSecret) . " ký tự</p>";
    echo "<p><strong>Starts with:</strong> <code>" . substr($clientSecret, 0, 10) . "...</code></p>";
    
    // Check định dạng Secret (thường là chữ + số + - + _)
    if (preg_match('/^[A-Za-z0-9_-]+$/', $clientSecret)) {
        echo "<p class='success'>✓ Định dạng hợp lệ</p>";
    } else {
        echo "<p class='warning'>⚠️ Client Secret có ký tự không hợp lệ</p>";
    }
}
echo "</div></div>";
echo "</div>";

// Kiểm tra Redirect URI
echo "<div class='info-box'>";
echo "<h3>3️⃣ Redirect URI</h3>";
echo "<div class='check-item'>";
echo "<span class='check-icon'>🌐</span>";
echo "<div>";
echo "<p><strong>URI hiện tại:</strong></p>";
echo "<code>" . GOOGLE_REDIRECT_URI . "</code>";
echo "<p style='margin-top:10px;'><strong>Lưu ý:</strong> URI này phải khớp CHÍNH XÁC với URI trong Google Console (không space, không trailing slash)</p>";
echo "</div>";
echo "</div>";
echo "</div>";

// Test authorization URL
echo "<div class='info-box'>";
echo "<h3>4️⃣ Authorization URL</h3>";
try {
    $authUrl = getGoogleAuthUrl() . '&state=google';
    echo "<p class='success'>✓ URL được tạo thành công</p>";
    echo "<textarea rows='4' readonly>" . $authUrl . "</textarea>";
    echo "<p style='margin-top:10px;'><strong>Giải thích:</strong></p>";
    echo "<ul>";
    echo "<li><code>client_id</code>: " . (strlen(GOOGLE_CLIENT_ID) > 20 ? '✅ Có' : '❌ Thiếu') . "</li>";
    echo "<li><code>redirect_uri</code>: " . GOOGLE_REDIRECT_URI . "</li>";
    echo "<li><code>response_type</code>: code</li>";
    echo "<li><code>scope</code>: email profile</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Lỗi khi tạo URL: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Hướng dẫn fix
echo "<div class='info-box'>";
echo "<h3>📋 Hướng Dẫn Fix Lỗi 401: invalid_client</h3>";

echo "<div class='step'>";
echo "<strong>Bước 1:</strong> Truy cập Google Cloud Console<br>";
echo "<a href='https://console.cloud.google.com/apis/credentials' target='_blank'>https://console.cloud.google.com/apis/credentials</a>";
echo "</div>";

echo "<div class='step'>";
echo "<strong>Bước 2:</strong> Click vào OAuth 2.0 Client ID của bạn<br>";
echo "Tìm Client ID trong danh sách và click vào tên để xem chi tiết.";
echo "</div>";

echo "<div class='step'>";
echo "<strong>Bước 3:</strong> Copy Client ID<br>";
echo "• Click vào icon copy bên cạnh Client ID<br>";
echo "• Đảm bảo copy toàn bộ, không thiếu ký tự nào<br>";
echo "• Client ID thường có dạng: <code>123456789-abc...xyz.apps.googleusercontent.com</code>";
echo "</div>";

echo "<div class='step'>";
echo "<strong>Bước 4:</strong> Copy Client Secret<br>";
echo "• Click vào icon copy bên cạnh Client Secret<br>";
echo "• Client Secret thường ngắn hơn, chỉ chữ + số + dấu gạch<br>";
echo "• Ví dụ: <code>GOCSPX-1a2b3c4d5e6f7g8h9i0j</code>";
echo "</div>";

echo "<div class='step'>";
echo "<strong>Bước 5:</strong> Paste vào file <code>config/oauth.php</code><br>";
echo "• Mở file: <code>config/oauth.php</code><br>";
echo "• Tìm dòng: <code>define('GOOGLE_CLIENT_ID', '...');</code><br>";
echo "• Thay thế giá trị cũ bằng Client ID vừa copy<br>";
echo "• Tương tự cho Client Secret<br>";
echo "• <strong>Lưu file</strong>";
echo "</div>";

echo "<div class='step'>";
echo "<strong>Bước 6:</strong> Test lại<br>";
echo "• Clear cache trình duyệt (Ctrl+Shift+Del)<br>";
echo "• Truy cập lại trang đăng nhập<br>";
echo "• Click 'Đăng nhập bằng Google'<br>";
echo "• Nếu vẫn lỗi → Double check Client ID & Secret";
echo "</div>";
echo "</div>";

// Checklist
echo "<div class='info-box'>";
echo "<h3>✅ Checklist</h3>";
echo "<ul style='line-height: 2;'>";
echo "<li>" . (GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID' ? '✅' : '❌') . " Client ID đã được cấu hình</li>";
echo "<li>" . (GOOGLE_CLIENT_SECRET !== 'YOUR_GOOGLE_CLIENT_SECRET' ? '✅' : '❌') . " Client Secret đã được cấu hình</li>";
echo "<li>" . (strpos(GOOGLE_CLIENT_ID, '.apps.googleusercontent.com') !== false || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' ? '✅' : '⚠️') . " Client ID có định dạng đúng</li>";
echo "<li>" . (file_exists(__DIR__ . '/oauth-callback.php') ? '✅' : '❌') . " File oauth-callback.php tồn tại</li>";
echo "<li>" . (file_exists(__DIR__ . '/../database/hotel.db') ? '✅' : '❌') . " Database đã khởi tạo</li>";
echo "</ul>";
echo "</div>";

// Buttons
echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='login.php' class='btn'>🔑 Thử Đăng Nhập</a>";
echo "<a href='check-oauth-setup.php' class='btn' style='background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);'>📋 Check Setup Đầy Đủ</a>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
