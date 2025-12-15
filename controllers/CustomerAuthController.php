<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/oauth.php';

class CustomerAuthController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function handleGoogleCallback($code) {
        // 1. Exchange code for token
        $ch = curl_init(GOOGLE_TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'code' => $code,
                'client_id' => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'grant_type' => 'authorization_code'
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $tokenInfo = json_decode($response, true);
        
        if (!isset($tokenInfo['access_token'])) {
            error_log("Google token error: " . print_r($tokenInfo, true));
            return false;
        }
        
        // 2. Get user info
        $ch = curl_init(GOOGLE_USERINFO_URL . '?access_token=' . $tokenInfo['access_token']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $userInfo = json_decode($response, true);
        
        if (!isset($userInfo['id'])) {
            error_log("Google userinfo error: " . print_r($userInfo, true));
            return false;
        }
        
        // 3. Save to database
        return $this->saveUser($userInfo, 'google', $tokenInfo['access_token']);
    }
    
    public function handleFacebookCallback($code) {
        try {
            error_log("=== handleFacebookCallback Start ===");
            
            // 1. Exchange code for token
            $tokenUrl = FACEBOOK_TOKEN_URL . '?' . http_build_query([
                'client_id' => FACEBOOK_APP_ID,
                'client_secret' => FACEBOOK_APP_SECRET,
                'redirect_uri' => FACEBOOK_REDIRECT_URI,
                'code' => $code
            ]);
            
            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $tokenInfo = json_decode($response, true);
            
            if (!isset($tokenInfo['access_token'])) {
                error_log("Facebook token error: " . print_r($tokenInfo, true));
                return false;
            }
            
            error_log("✓ Facebook access token received");
            
            // 2. Get user info with specific fields
            $userInfoUrl = FACEBOOK_USERINFO_URL . '?' . http_build_query([
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $tokenInfo['access_token']
            ]);
            
            $ch = curl_init($userInfoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $userInfo = json_decode($response, true);
            
            if (!isset($userInfo['id'])) {
                error_log("Facebook userinfo error: " . print_r($userInfo, true));
                return false;
            }
            
            error_log("✓ Facebook user info received: " . $userInfo['name']);
            
            // 3. Save to database
            $providerId = $userInfo['id'];
            $name = $userInfo['name'];
            
            // Email handling - QUAN TRỌNG: không dùng constraint UNIQUE
            // Nếu Facebook không trả về email, tạo email unique với timestamp
            if (isset($userInfo['email']) && !empty($userInfo['email'])) {
                $email = $userInfo['email'];
            } else {
                // Tạo email unique với timestamp để tránh conflict
                $email = 'fb_' . $providerId . '_' . time() . '@facebook.user';
            }
            
            // Avatar từ Facebook
            $avatar = isset($userInfo['picture']['data']['url']) 
                ? $userInfo['picture']['data']['url'] 
                : 'https://ui-avatars.com/api/?name=' . urlencode($name);
            
            error_log("Processing Facebook user: ID=$providerId, Email=$email, Name=$name");
            
            // Check if user exists - tìm theo Provider và ProviderId, KHÔNG tìm theo Email
            $stmt = $this->db->prepare("SELECT * FROM KHACHHANG_USERS WHERE Provider = ? AND ProviderId = ?");
            $stmt->execute(['facebook', $providerId]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update existing user
                error_log("✓ Updating existing Facebook user ID: " . $user['MaKhachHangUser']);
                
                $stmt = $this->db->prepare("UPDATE KHACHHANG_USERS 
                    SET TenHienThi = ?, Avatar = ?, AccessToken = ?, LanDangNhapCuoi = CURRENT_TIMESTAMP 
                    WHERE MaKhachHangUser = ?");
                $stmt->execute([$name, $avatar, $tokenInfo['access_token'], $user['MaKhachHangUser']]);
                $userId = $user['MaKhachHangUser'];
            } else {
                // Create new user
                error_log("✓ Creating new Facebook user");
                
                // Double check email không trùng (trong trường hợp hiếm)
                $stmt = $this->db->prepare("SELECT MaKhachHangUser FROM KHACHHANG_USERS WHERE Email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    // Email đã tồn tại, thêm random suffix
                    $email = 'fb_' . $providerId . '_' . time() . '_' . rand(1000, 9999) . '@facebook.user';
                    error_log("Email conflict detected, using: $email");
                }
                
                $stmt = $this->db->prepare("INSERT INTO KHACHHANG_USERS 
                    (Email, TenHienThi, Avatar, Provider, ProviderId, AccessToken, TrangThai) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Hoạt động')");
                $stmt->execute([$email, $name, $avatar, 'facebook', $providerId, $tokenInfo['access_token']]);
                $userId = $this->db->lastInsertId();
                
                error_log("✓ Created new Facebook user ID: $userId");
            }
            
            // Save to session
            $_SESSION['customer_id'] = $userId;
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_avatar'] = $avatar;
            $_SESSION['customer_provider'] = 'facebook';
            
            error_log("✓ Facebook login successful - User ID: $userId");
            error_log("=== handleFacebookCallback Success ===");
            
            return $userId;
            
        } catch (Exception $e) {
            error_log("✗ Exception in handleFacebookCallback: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    private function saveUser($userInfo, $provider, $accessToken) {
        $providerId = $userInfo['id'];
        $name = $userInfo['name'];
        
        if ($provider === 'google') {
            $email = $userInfo['email'] ?? '';
            $avatar = $userInfo['picture'] ?? '';
        } else {
            $email = $userInfo['email'] ?? ($providerId . '@facebook.user');
            $avatar = $userInfo['picture']['data']['url'] ?? '';
        }
        
        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM KHACHHANG_USERS WHERE Provider = ? AND ProviderId = ?");
        $stmt->execute([$provider, $providerId]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update existing user
            $stmt = $this->db->prepare("UPDATE KHACHHANG_USERS 
                SET Email = ?, TenHienThi = ?, Avatar = ?, AccessToken = ?, LanDangNhapCuoi = CURRENT_TIMESTAMP 
                WHERE MaKhachHangUser = ?");
            $stmt->execute([$email, $name, $avatar, $accessToken, $user['MaKhachHangUser']]);
            $userId = $user['MaKhachHangUser'];
        } else {
            // Create new user
            $stmt = $this->db->prepare("INSERT INTO KHACHHANG_USERS 
                (Email, TenHienThi, Avatar, Provider, ProviderId, AccessToken) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$email, $name, $avatar, $provider, $providerId, $accessToken]);
            $userId = $this->db->lastInsertId();
        }
        
        // Save to session
        $_SESSION['customer_id'] = $userId;
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_avatar'] = $avatar;
        $_SESSION['customer_provider'] = $provider;
        
        return $userId;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['customer_id']);
    }
    
    public function getCustomerInfo() {
        if (!$this->isLoggedIn()) return null;
        
        return [
            'id' => $_SESSION['customer_id'],
            'name' => $_SESSION['customer_name'],
            'email' => $_SESSION['customer_email'],
            'avatar' => $_SESSION['customer_avatar'],
            'provider' => $_SESSION['customer_provider']
        ];
    }
    
    public function logout() {
        session_unset();
        session_destroy();
    }
}
?>
