<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/oauth.php';

class CustomerAuthController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Xử lý callback từ Google
    public function handleGoogleCallback($code) {
        try {
            error_log("=== handleGoogleCallback Start ===");
            error_log("Authorization code: " . substr($code, 0, 20) . "...");
            
            // Lấy access token
            $tokenData = [
                'code' => $code,
                'client_id' => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'grant_type' => 'authorization_code'
            ];
            
            error_log("Token request data: " . print_r([
                'url' => GOOGLE_TOKEN_URL,
                'client_id' => GOOGLE_CLIENT_ID,
                'redirect_uri' => GOOGLE_REDIRECT_URI
            ], true));
            
            // Sử dụng CURL
            $ch = curl_init(GOOGLE_TOKEN_URL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            // Removed curl_close() - deprecated in PHP 8.5
            
            error_log("Token response HTTP code: " . $httpCode);
            error_log("Token response body: " . $response);
            
            if ($curlError) {
                error_log("CURL error: " . $curlError);
                return false;
            }
            
            if ($response === false || $httpCode !== 200) {
                error_log("Failed to get token - HTTP " . $httpCode);
                return false;
            }
            
            $tokenInfo = json_decode($response, true);
            
            if (isset($tokenInfo['error'])) {
                error_log("Token error: " . $tokenInfo['error'] . " - " . ($tokenInfo['error_description'] ?? ''));
                return false;
            }
            
            if (!isset($tokenInfo['access_token'])) {
                error_log("No access_token in response");
                return false;
            }
            
            error_log("Access token received: " . substr($tokenInfo['access_token'], 0, 20) . "...");
            
            // Lấy thông tin user từ Google
            $userInfo = $this->getGoogleUserInfo($tokenInfo['access_token']);
            
            if (!$userInfo) {
                error_log("Failed to get user info");
                return false;
            }
            
            error_log("User info received: " . print_r($userInfo, true));
            
            // Lưu hoặc cập nhật user trong database
            return $this->saveOrUpdateUser($userInfo, 'google', $tokenInfo);
            
        } catch (Exception $e) {
            error_log("Exception in handleGoogleCallback: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    // Lấy thông tin user từ Google
    private function getGoogleUserInfo($accessToken) {
        try {
            error_log("=== getGoogleUserInfo Start ===");
            error_log("Access token: " . substr($accessToken, 0, 20) . "...");
            
            $ch = curl_init(GOOGLE_USERINFO_URL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            // Removed curl_close()
            
            error_log("UserInfo response HTTP code: " . $httpCode);
            error_log("UserInfo response body: " . $response);
            
            if ($curlError) {
                error_log("CURL error: " . $curlError);
                return false;
            }
            
            if ($response === false || $httpCode !== 200) {
                error_log("Failed to get user info - HTTP " . $httpCode);
                return false;
            }
            
            $userInfo = json_decode($response, true);
            
            if (isset($userInfo['error'])) {
                error_log("UserInfo error: " . json_encode($userInfo['error']));
                return false;
            }
            
            return $userInfo;
        } catch (Exception $e) {
            error_log("Exception in getGoogleUserInfo: " . $e->getMessage());
            return false;
        }
    }
    
    // Xử lý callback từ Facebook
    public function handleFacebookCallback($code) {
        try {
            $tokenUrl = FACEBOOK_TOKEN_URL;
            $tokenUrl .= '?client_id=' . FACEBOOK_APP_ID;
            $tokenUrl .= '&redirect_uri=' . urlencode(FACEBOOK_REDIRECT_URI);
            $tokenUrl .= '&client_secret=' . FACEBOOK_APP_SECRET;
            $tokenUrl .= '&code=' . $code;
            
            $response = file_get_contents($tokenUrl);
            
            if ($response === false) {
                error_log("Failed to get token from Facebook");
                return false;
            }
            
            $tokenInfo = json_decode($response, true);
            
            if (isset($tokenInfo['access_token'])) {
                $userInfo = $this->getFacebookUserInfo($tokenInfo['access_token']);
                
                if ($userInfo) {
                    return $this->saveOrUpdateUser($userInfo, 'facebook', $tokenInfo);
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in handleFacebookCallback: " . $e->getMessage());
            return false;
        }
    }
    
    // Lấy thông tin user từ Facebook
    private function getFacebookUserInfo($accessToken) {
        try {
            $userInfoUrl = FACEBOOK_USERINFO_URL . '?fields=id,name,email,picture&access_token=' . $accessToken;
            $response = file_get_contents($userInfoUrl);
            
            if ($response === false) {
                error_log("Failed to get user info from Facebook");
                return false;
            }
            
            return json_decode($response, true);
        } catch (Exception $e) {
            error_log("Error in getFacebookUserInfo: " . $e->getMessage());
            return false;
        }
    }
    
    // Lưu hoặc cập nhật user trong database
    private function saveOrUpdateUser($userInfo, $provider, $tokenInfo) {
        try {
            error_log("=== saveOrUpdateUser Start ===");
            
            $providerId = $userInfo['id'];
            $email = $userInfo['email'] ?? '';
            $name = $userInfo['name'];
            $avatar = $provider === 'google' ? $userInfo['picture'] : $userInfo['picture']['data']['url'];
            
            error_log("User data - Provider: $provider, ID: $providerId, Email: $email, Name: $name");
            
            // Kiểm tra bảng KHACHHANG_USERS có tồn tại không
            try {
                $this->db->query("SELECT 1 FROM KHACHHANG_USERS LIMIT 1");
            } catch (Exception $e) {
                error_log("Table KHACHHANG_USERS not found: " . $e->getMessage());
                error_log("Please run database/init.php to create tables");
                return false;
            }
            
            // Kiểm tra user đã tồn tại chưa
            $stmt = $this->db->prepare("SELECT * FROM KHACHHANG_USERS WHERE Provider = ? AND ProviderId = ?");
            $stmt->execute([$provider, $providerId]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                error_log("User exists, updating...");
                // Cập nhật thông tin
                $stmt = $this->db->prepare("UPDATE KHACHHANG_USERS 
                                           SET Email = ?, TenHienThi = ?, Avatar = ?, 
                                               AccessToken = ?, LanDangNhapCuoi = CURRENT_TIMESTAMP
                                           WHERE MaKhachHangUser = ?");
                $stmt->execute([
                    $email, 
                    $name, 
                    $avatar, 
                    $tokenInfo['access_token'],
                    $existingUser['MaKhachHangUser']
                ]);
                $userId = $existingUser['MaKhachHangUser'];
                error_log("Updated existing user ID: $userId");
            } else {
                error_log("Creating new user...");
                // Tạo user mới
                $stmt = $this->db->prepare("INSERT INTO KHACHHANG_USERS 
                                           (Email, TenHienThi, Avatar, Provider, ProviderId, AccessToken, RefreshToken)
                                           VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $email,
                    $name,
                    $avatar,
                    $provider,
                    $providerId,
                    $tokenInfo['access_token'],
                    $tokenInfo['refresh_token'] ?? null
                ]);
                $userId = $this->db->lastInsertId();
                error_log("Created new user ID: $userId");
            }
            
            // Lưu session
            $_SESSION['customer_id'] = $userId;
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_avatar'] = $avatar;
            $_SESSION['customer_provider'] = $provider;
            
            error_log("Session saved: customer_id=$userId, name=$name, email=$email");
            error_log("=== saveOrUpdateUser Success ===");
            
            return $userId;
        } catch (Exception $e) {
            error_log("Exception in saveOrUpdateUser: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    // Kiểm tra đăng nhập
    public function isLoggedIn() {
        return isset($_SESSION['customer_id']);
    }
    
    // Lấy thông tin khách hàng đã đăng nhập
    public function getCustomerInfo() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['customer_id'],
            'name' => $_SESSION['customer_name'],
            'email' => $_SESSION['customer_email'],
            'avatar' => $_SESSION['customer_avatar'],
            'provider' => $_SESSION['customer_provider']
        ];
    }
    
    // Đăng xuất
    public function logout() {
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_avatar']);
        unset($_SESSION['customer_provider']);
    }
}
