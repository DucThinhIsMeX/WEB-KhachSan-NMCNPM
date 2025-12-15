# Khắc Phục Lỗi OAuth "access_type can only have a single value"

## Nguyên nhân

Lỗi này xảy ra khi:
1. Tham số `access_type` được gửi nhiều lần trong URL
2. URL OAuth không được build đúng cách
3. Conflict giữa các tham số OAuth 2.0

## Giải pháp

### 1. Kiểm tra file config/oauth.php

Đảm bảo không có duplicate parameters:

```php
// ❌ SAI - Duplicate access_type
$url = GOOGLE_AUTH_URL . '?access_type=offline&access_type=online';

// ✅ ĐÚNG - Single value only
$params = ['access_type' => 'offline'];
$url = GOOGLE_AUTH_URL . '?' . http_build_query($params);
```

### 2. Sử dụng http_build_query()

Luôn dùng `http_build_query()` để tạo query string an toàn:

```php
function getGoogleAuthUrl() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'offline', // Chỉ 1 giá trị
        'prompt' => 'consent'
    ];
    
    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}
```

### 3. Clear browser cache

Sau khi fix code:
1. Clear cookies/cache của trình duyệt
2. Restart PHP server
3. Thử đăng nhập lại

### 4. Kiểm tra logs

Xem file error log để debug:

```bash
# Xem PHP error log
tail -f /var/log/php_errors.log

# Hoặc check trong browser Console (F12)
```

### 5. Verify Google OAuth Config

Trong Google Cloud Console:
- Kiểm tra Redirect URI khớp chính xác
- Đảm bảo OAuth consent screen đã được cấu hình
- Scope cần có `email` và `profile`

## Test thủ công

Kiểm tra URL OAuth được tạo:

```php
<?php
require_once 'config/oauth.php';
echo getGoogleAuthUrl();
?>
```

URL đúng sẽ có dạng:
```
https://accounts.google.com/o/oauth2/v2/auth?
client_id=xxx&
redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fcustomer%2Foauth-callback.php&
response_type=code&
scope=email+profile&
access_type=offline&
prompt=consent
```

**Lưu ý:** Mỗi tham số chỉ xuất hiện 1 lần!

## Vẫn gặp lỗi?

1. Kiểm tra PHP version >= 7.4
2. Verify `curl` extension enabled
3. Check firewall/proxy settings
4. Test với incognito mode
5. Tạo lại OAuth credentials trên Google Cloud Console
