<?php
/**
 * Production Configuration
 * Sử dụng file này khi deploy lên production
 */

// Error Reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
error_reporting(E_ALL);

// Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Enable nếu dùng HTTPS

// Database Configuration
define('DB_TYPE', 'sqlite'); // hoặc 'mysql' nếu chuyển sang MySQL
define('DB_FILE', __DIR__ . '/../database/hotel.db');

// MySQL Config (nếu dùng)
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_db');
define('DB_USER', 'hotel_user');
define('DB_PASS', 'secure_password');

// Application Settings
define('APP_NAME', 'Hotel Management System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// Paths
define('APP_URL', 'https://yourdomain.com');
define('ADMIN_URL', APP_URL . '/admin');
define('ASSETS_URL', APP_URL . '/assets');

// File Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Logging
define('LOG_FILE', __DIR__ . '/../logs/app.log');
define('LOG_LEVEL', 'error'); // error, warning, info, debug

// Cache
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Email (nếu cần)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@hotel.com');

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '416938682838-6ohqmd704l8v07ved380didth1feauqm.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', APP_URL . '/customer/oauth-callback.php');

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', '851970674092651');
define('FACEBOOK_APP_SECRET', '19c7a3b759084aa9d821cc6d5346361e');
define('FACEBOOK_REDIRECT_URI', APP_URL . '/auth/facebook/callback');

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
