<?php
/**
 * OAuth Configuration Template
 * 
 * Hướng dẫn:
 * 1. Copy file này thành oauth.php
 * 2. Điền các thông tin OAuth credentials của bạn
 * 3. KHÔNG commit file oauth.php lên Git
 */

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost:8000/customer/google-callback.php');

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET');
define('FACEBOOK_REDIRECT_URI', 'http://localhost:8000/customer/facebook-callback.php');

// OAuth URLs (giữ nguyên)
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

define('FACEBOOK_AUTH_URL', 'https://www.facebook.com/v18.0/dialog/oauth');
define('FACEBOOK_TOKEN_URL', 'https://graph.facebook.com/v18.0/oauth/access_token');
define('FACEBOOK_USERINFO_URL', 'https://graph.facebook.com/me');
