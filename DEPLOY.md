# 🚀 HƯỚNG DẪN DEPLOY DỰ ÁN

## 📋 MỤC LỤC

1. [Deploy Local (Localhost)](#1-deploy-local-localhost)
2. [Deploy lên Hosting Miễn Phí](#2-deploy-lên-hosting-miễn-phí)
3. [Deploy lên VPS/Server](#3-deploy-lên-vpsserver)
4. [Deploy với XAMPP/WAMP](#4-deploy-với-xamppwamp)

---

## 1. DEPLOY LOCAL (LOCALHOST)

### Yêu Cầu Hệ Thống
- PHP >= 7.4
- SQLite3 extension enabled
- Web browser (Chrome, Firefox, Edge)

### Cách 1: Sử dụng PHP Built-in Server (Khuyến nghị cho dev)

```bash
# Bước 1: Mở terminal/cmd tại thư mục dự án
cd "c:\Users\Duc Thinh\Documents\Nhập môn CNPM\DOAN\WEB-KhachSan-NMCNPM"

# Bước 2: Khởi tạo database (chỉ lần đầu)
php database/init.php

# Bước 3: Chạy server
php -S localhost:8000

# Bước 4: Truy cập
# Trang khách: http://localhost:8000
# Trang admin: http://localhost:8000/admin
```

### Cách 2: Sử dụng XAMPP/WAMP

```bash
# Bước 1: Copy toàn bộ dự án vào thư mục htdocs/www
# XAMPP: C:\xampp\htdocs\hotel
# WAMP: C:\wamp64\www\hotel

# Bước 2: Truy cập
# http://localhost/hotel
# http://localhost/hotel/admin
```

---

## 2. DEPLOY LÊN HOSTING MIỄN PHÍ

### A. InfinityFree (Khuyến nghị)

**Website:** https://infinityfree.net

#### Bước 1: Đăng ký tài khoản
1. Truy cập infinityfree.net
2. Click "Sign Up"
3. Tạo tài khoản miễn phí

#### Bước 2: Tạo hosting account
1. Chọn "Create Account"
2. Nhập subdomain: `yourdomain.infinityfreeapp.com`
3. Chọn gói miễn phí
4. Đợi kích hoạt (5-10 phút)

#### Bước 3: Upload code
```bash
# Cách 1: FTP Client (FileZilla)
# - Tải FileZilla: https://filezilla-project.org
# - Host: ftpupload.net
# - Username: epiz_xxxxx
# - Password: [mật khẩu bạn đặt]
# - Port: 21

# Cách 2: File Manager (trên Control Panel)
# - Login vào VistaPanel
# - Online File Manager
# - Upload toàn bộ file vào thư mục htdocs
```

#### Bước 4: Setup database
```sql
-- Vì InfinityFree hỗ trợ MySQL, cần convert từ SQLite

-- 1. Tạo database trên hosting
-- 2. Import file SQL (sẽ tạo ở bước sau)
-- 3. Cập nhật config/database.php
```

#### Bước 5: Cấu hình
```php
// Tạo file config/database_mysql.php
<?php
class Database {
    private $host = "sql123.infinityfree.com";
    private $db_name = "epiz_xxxxx_hotel";
    private $username = "epiz_xxxxx";
    private $password = "your_password";
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
```

### B. 000webhost.com

**Website:** https://www.000webhost.com

```bash
# Tương tự InfinityFree nhưng:
# - Hỗ trợ PHP 7.4+
# - MySQL database
# - Free SSL certificate
# - Upload qua FTP hoặc File Manager
```

### C. Hostinger Free

**Website:** https://www.hostinger.vn/hosting-mien-phi

```bash
# - Tốc độ nhanh hơn
# - Giới hạn 100MB dung lượng
# - Không có ads
```

---

## 3. DEPLOY LÊN VPS/SERVER

### A. Yêu Cầu Server
- Ubuntu 20.04+ / CentOS 7+
- PHP 7.4+
- Apache/Nginx
- SQLite3

### B. Cài Đặt Trên Ubuntu

```bash
# Bước 1: Cập nhật hệ thống
sudo apt update
sudo apt upgrade -y

# Bước 2: Cài đặt Apache
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2

# Bước 3: Cài đặt PHP
sudo apt install php php-cli php-sqlite3 php-mbstring -y

# Bước 4: Clone dự án
cd /var/www/html
sudo git clone [your-repo-url] hotel
# Hoặc upload qua FTP/SFTP

# Bước 5: Phân quyền
sudo chown -R www-data:www-data hotel
sudo chmod -R 755 hotel
sudo chmod -R 777 hotel/database

# Bước 6: Cấu hình Apache
sudo nano /etc/apache2/sites-available/hotel.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/hotel
    
    <Directory /var/www/html/hotel>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/hotel-error.log
    CustomLog ${APACHE_LOG_DIR}/hotel-access.log combined
</VirtualHost>
```

```bash
# Bước 7: Enable site
sudo a2ensite hotel.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# Bước 8: Setup SSL (Let's Encrypt)
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

---

## 4. DEPLOY VỚI XAMPP/WAMP (CHI TIẾT)

### A. XAMPP

```bash
# Bước 1: Tải XAMPP
# https://www.apachefriends.org/download.html

# Bước 2: Cài đặt XAMPP
# - Chọn Apache + PHP
# - Cài vào C:\xampp

# Bước 3: Copy dự án
# Copy toàn bộ folder vào: C:\xampp\htdocs\hotel

# Bước 4: Khởi động
# - Mở XAMPP Control Panel
# - Start Apache
# - Truy cập: http://localhost/hotel

# Bước 5: Cấu hình Virtual Host (tùy chọn)
# File: C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/hotel"
    ServerName hotel.local
    <Directory "C:/xampp/htdocs/hotel">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
# Bước 6: Sửa file hosts
# File: C:\Windows\System32\drivers\etc\hosts
# Thêm dòng:
127.0.0.1 hotel.local

# Bước 7: Restart Apache
# Truy cập: http://hotel.local
```

---

## 5. CHUYỂN ĐỔI SQLite SANG MySQL

### Tạo file export SQL

```bash
# Bước 1: Export SQLite to SQL
sqlite3 database/hotel.db .dump > hotel.sql

# Bước 2: Chỉnh sửa file hotel.sql
# - Xóa các dòng BEGIN TRANSACTION, COMMIT
# - Thay AUTOINCREMENT thành AUTO_INCREMENT
# - Thay INTEGER PRIMARY KEY thành INT AUTO_INCREMENT
```

### File migration script

```php
<?php
// filepath: database/migrate_to_mysql.php
// Script convert SQLite to MySQL

$sqlite = new PDO('sqlite:hotel.db');
$mysql = new PDO('mysql:host=localhost;dbname=hotel', 'root', 'password');

// Export tables
$tables = ['LOAIPHONG', 'PHONG', 'KHACHHANG', 'PHIEUTHUE', 
           'CHITIET_THUE', 'HOADON', 'CHITIET_HOADON', 
           'BAOCAO_DOANHTHU', 'CHITIET_BAOCAO', 'THAMSO'];

foreach ($tables as $table) {
    $rows = $sqlite->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {
        $columns = implode(', ', array_keys($row));
        $placeholders = implode(', ', array_fill(0, count($row), '?'));
        
        $stmt = $mysql->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($row));
    }
}

echo "Migration completed!";
?>
```

---

## 6. TỐI ƯU HÓA CHO PRODUCTION

### A. Tạo file .htaccess

```apache
# filepath: .htaccess
# Bảo mật và tối ưu

# Bảo vệ database
<Files "hotel.db">
    Order Allow,Deny
    Deny from all
</Files>

# Bảo vệ config
<FilesMatch "\.php$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# Rewrite rules
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

### B. Minify CSS/JS

```bash
# Sử dụng online tools:
# - https://www.minifier.org/
# - https://cssminifier.com/
# - https://javascript-minifier.com/

# Hoặc npm:
npm install -g clean-css-cli uglify-js
cleancss -o assets/css/style.min.css assets/css/style.css
uglifyjs assets/js/app.js -o assets/js/app.min.js
```

---

## 7. CHECKLIST TRƯỚC KHI DEPLOY

- [ ] Test tất cả chức năng trên local
- [ ] Backup database
- [ ] Remove debug code, console.log
- [ ] Check file permissions
- [ ] Enable error reporting = Off (production)
- [ ] Setup .htaccess security
- [ ] Minify CSS/JS
- [ ] Optimize images
- [ ] Setup SSL certificate
- [ ] Test trên nhiều browsers
- [ ] Test responsive mobile
- [ ] Setup backup tự động
- [ ] Monitor error logs

---

## 8. TROUBLESHOOTING

### Lỗi thường gặp:

**1. Database connection failed**
```bash
# Kiểm tra:
- File hotel.db có tồn tại?
- Folder database có quyền write? (chmod 777)
- SQLite3 extension enabled?
```

**2. 404 Not Found**
```bash
# Kiểm tra:
- mod_rewrite enabled? (Apache)
- .htaccess có tồn tại?
- AllowOverride All trong VirtualHost?
```

**3. Permission denied**
```bash
# Fix:
sudo chown -R www-data:www-data /var/www/html/hotel
sudo chmod -R 755 /var/www/html/hotel
sudo chmod -R 777 /var/www/html/hotel/database
```

**4. Blank page**
```bash
# Enable error reporting:
ini_set('display_errors', 1);
error_reporting(E_ALL);

# Check error log:
tail -f /var/log/apache2/error.log
```

---

## 9. BẢO MẬT

### A. Bảo vệ admin area

```php
// filepath: admin/auth.php
<?php
session_start();

// Simple authentication
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Hardcoded for demo (use database in production)
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: index.php');
            exit;
        }
    }
    
    // Show login form
    include 'login.php';
    exit;
}
?>
```

### B. Validate input

```php
// Luôn validate và sanitize input
$input = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
```

### C. Prevent SQL Injection

```php
// Luôn dùng prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

---

## 10. MONITORING & MAINTENANCE

### A. Setup logging

```php
// filepath: config/logger.php
<?php
function logError($message) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
?>
```

### B. Backup script

```bash
#!/bin/bash
# filepath: backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"

# Backup database
cp database/hotel.db $BACKUP_DIR/hotel_$DATE.db

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz .

# Keep only 7 days
find $BACKUP_DIR -name "*.db" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

### C. Cron job

```bash
# Chạy backup mỗi ngày lúc 2h sáng
0 2 * * * /path/to/backup.sh
```

---

## 📞 HỖ TRỢ

- **Documentation:** README.md, HUONG-DAN-SU-DUNG.md
- **Issues:** Tạo issue trên GitHub
- **Email:** support@hotel.com

---

## 🎉 DONE!

Sau khi deploy thành công:
1. Test tất cả chức năng
2. Setup monitoring
3. Enable backup tự động
4. Document domain/URL
5. Share với team

**Good luck! 🚀**
