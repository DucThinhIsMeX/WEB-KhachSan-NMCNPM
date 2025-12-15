# 🔒 Security Guidelines

## Bảo mật OAuth Credentials

### ⚠️ QUAN TRỌNG

File `config/oauth.php` chứa **Client Secret** - đây là thông tin nhạy cảm!

**KHÔNG BAO GIỜ:**
- ❌ Commit file `config/oauth.php` lên GitHub public repo
- ❌ Share Client Secret trên diễn đàn/chat công khai
- ❌ Hard-code credentials trong code
- ❌ Để credentials trong file không được bảo vệ

### ✅ Best Practices

1. **Sử dụng .gitignore:**
   ```gitignore
   config/oauth.php
   config/production.php
   .env
   .env.local
   ```

2. **Sử dụng Environment Variables:**
   ```php
   define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
   define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
   ```

3. **File .env (không commit):**
   ```bash
   GOOGLE_CLIENT_ID=your_client_id
   GOOGLE_CLIENT_SECRET=your_client_secret
   ```

4. **File .env.example (có thể commit):**
   ```bash
   GOOGLE_CLIENT_ID=your_client_id_here
   GOOGLE_CLIENT_SECRET=your_client_secret_here
   ```

## Nếu Client Secret bị lộ

1. **Ngay lập tức:**
   - Truy cập Google Cloud Console
   - Xóa OAuth Client cũ
   - Tạo OAuth Client mới
   - Cập nhật credentials mới vào code

2. **Thay đổi:**
   ```php
   // config/oauth.php
   define('GOOGLE_CLIENT_ID', 'NEW_CLIENT_ID');
   define('GOOGLE_CLIENT_SECRET', 'NEW_CLIENT_SECRET');
   ```

3. **Kiểm tra logs:**
   - Xem có hoạt động bất thường không
   - Monitor API usage trên Google Console

## Permissions

Đảm bảo file permissions đúng:

```bash
# Linux/macOS
chmod 600 config/oauth.php
chmod 600 config/production.php
chmod 600 .env

# Chỉ owner có quyền read/write
```

## Production Security

Khi deploy lên production:

1. **Enable HTTPS:**
   ```php
   ini_set('session.cookie_secure', 1);
   ```

2. **Update Redirect URI:**
   ```
   https://yourdomain.com/customer/oauth-callback.php
   ```

3. **Restrict Origins:**
   - Chỉ allow origins từ domain của bạn
   - Cấu hình CORS headers

4. **Monitor:**
   - Enable Google Cloud Console audit logs
   - Track unauthorized access attempts

## Rate Limiting

Implement rate limiting cho OAuth endpoints:

```php
// Giới hạn 10 requests/minute/IP
$rateLimiter->check($_SERVER['REMOTE_ADDR'], 10, 60);
```

## Reporting Security Issues

Nếu phát hiện lỗ hổng bảo mật:
- Email: security@hotel.com
- Không public issue trên GitHub
- Chờ patch trước khi disclosure
