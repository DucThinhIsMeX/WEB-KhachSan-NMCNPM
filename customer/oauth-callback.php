<?php
session_start();
require_once __DIR__ . '/../config/oauth.php';
require_once __DIR__ . '/../config/database.php';

// Log để debug
error_log("=== OAuth Callback Start ===");
error_log("GET params: " . print_r($_GET, true));

// Kiểm tra lỗi
if (isset($_GET['error'])) {
    error_log("OAuth Error: " . $_GET['error']);
    $_SESSION['error'] = 'Đăng nhập bị từ chối: ' . ($_GET['error_description'] ?? $_GET['error']);
    header('Location: login.php');
    exit();
}

// Kiểm tra code
if (!isset($_GET['code'])) {
    error_log("No authorization code");
    $_SESSION['error'] = 'Không nhận được mã xác thực';
    header('Location: login.php');
    exit();
}

$code = $_GET['code'];
$state = $_GET['state'] ?? 'google'; // Mặc định là google

error_log("State: $state, Code: " . substr($code, 0, 20) . "...");

try {
    if ($state === 'facebook') {
        error_log("Processing Facebook OAuth...");
        
        // 1. Exchange code for token
        $token_url = FACEBOOK_TOKEN_URL . '?' . http_build_query([
            'client_id' => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'redirect_uri' => FACEBOOK_REDIRECT_URI,
            'code' => $code
        ]);
        
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        error_log("Token response (HTTP $httpCode): $response");
        
        $token_data = json_decode($response, true);
        
        if (!isset($token_data['access_token'])) {
            throw new Exception('Không lấy được access token từ Facebook: ' . ($token_data['error']['message'] ?? 'Unknown error'));
        }
        
        $access_token = $token_data['access_token'];
        error_log("✓ Got Facebook access token");
        
        // 2. Get user info
        $user_info_url = FACEBOOK_USERINFO_URL . '?' . http_build_query([
            'fields' => 'id,name,email,picture.type(large)',
            'access_token' => $access_token
        ]);
        
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        error_log("User info response: $response");
        
        $user_info = json_decode($response, true);
        
        if (!isset($user_info['id'])) {
            throw new Exception('Không lấy được thông tin user từ Facebook');
        }
        
        $provider = 'facebook';
        $provider_id = $user_info['id'];
        $name = $user_info['name'];
        $email = $user_info['email'] ?? 'fb_' . $provider_id . '_' . time() . '@facebook.user';
        $avatar = $user_info['picture']['data']['url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($name);
        
        error_log("✓ Facebook user: $name ($email)");
        
    } else {
        error_log("Processing Google OAuth...");
        
        // Xử lý Google OAuth
        $token_url = GOOGLE_TOKEN_URL;
        $post_data = [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $token_data = json_decode($response, true);
        
        if (!isset($token_data['access_token'])) {
            throw new Exception('Không lấy được access token từ Google');
        }
        
        $access_token = $token_data['access_token'];
        
        // Lấy thông tin user
        $user_info_url = GOOGLE_USERINFO_URL . '?access_token=' . $access_token;
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $user_info = json_decode($response, true);
        
        if (!isset($user_info['id'])) {
            throw new Exception('Không lấy được thông tin user từ Google');
        }
        
        $provider = 'google';
        $provider_id = $user_info['id'];
        $name = $user_info['name'];
        $email = $user_info['email'];
        $avatar = $user_info['picture'] ?? '';
    }
    
    // 3. Lưu vào database
    $db = (new Database())->connect();
    
    // Kiểm tra user đã tồn tại chưa
    $stmt = $db->prepare("SELECT * FROM KHACHHANG_USERS WHERE Provider = ? AND ProviderId = ?");
    $stmt->execute([$provider, $provider_id]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_user) {
        // Update existing user
        error_log("Updating existing user ID: " . $existing_user['MaKhachHangUser']);
        
        $stmt = $db->prepare("UPDATE KHACHHANG_USERS SET 
            TenHienThi = ?, Avatar = ?, AccessToken = ?, LanDangNhapCuoi = CURRENT_TIMESTAMP 
            WHERE MaKhachHangUser = ?");
        $stmt->execute([$name, $avatar, $access_token, $existing_user['MaKhachHangUser']]);
        $user_id = $existing_user['MaKhachHangUser'];
    } else {
        // Create new user
        error_log("Creating new user");
        
        // Check email uniqueness
        $stmt = $db->prepare("SELECT MaKhachHangUser FROM KHACHHANG_USERS WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $email = $provider . '_' . $provider_id . '_' . time() . '@oauth.user';
            error_log("Email conflict, using: $email");
        }
        
        $stmt = $db->prepare("INSERT INTO KHACHHANG_USERS 
            (Email, TenHienThi, Avatar, Provider, ProviderId, AccessToken, TrangThai) 
            VALUES (?, ?, ?, ?, ?, ?, 'Hoạt động')");
        $stmt->execute([$email, $name, $avatar, $provider, $provider_id, $access_token]);
        $user_id = $db->lastInsertId();
        
        error_log("✓ Created new user ID: $user_id");
    }
    
    // 4. Save to session
    $_SESSION['customer_id'] = $user_id;
    $_SESSION['customer_name'] = $name;
    $_SESSION['customer_email'] = $email;
    $_SESSION['customer_avatar'] = $avatar;
    $_SESSION['customer_provider'] = $provider;
    
    error_log("✓ Login successful - User ID: $user_id");
    error_log("Session: " . print_r($_SESSION, true));
    error_log("=== OAuth Callback Success ===");
    
    // 5. Redirect về trang chủ
    header('Location: ../index.php');
    exit();
    
} catch (Exception $e) {
    error_log("✗ Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['error'] = 'Lỗi đăng nhập: ' . $e->getMessage();
    header('Location: login.php');
    exit();
}
?>
