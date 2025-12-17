<?php
/**
 * OAuth Configuration
 * Cấu hình cho Google và Facebook OAuth
 */

// Google OAuth Configuration
// ✅ Client ID và Secret từ Google Cloud Console
define('GOOGLE_CLIENT_ID', '416938682838-6ohqmd704l8v07ved380didth1feauqm.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost:8000/customer/oauth-callback.php');
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Facebook OAuth Configuration (optional)
define('FACEBOOK_APP_ID', '851970674092651');
define('FACEBOOK_APP_SECRET', '19c7a3b759084aa9d821cc6d5346361e');
define('FACEBOOK_REDIRECT_URI', 'http://localhost:8000/customer/oauth-callback.php'); // ✅ Đổi thành oauth-callback.php
define('FACEBOOK_AUTH_URL', 'https://www.facebook.com/v18.0/dialog/oauth');
define('FACEBOOK_TOKEN_URL', 'https://graph.facebook.com/v18.0/oauth/access_token');
define('FACEBOOK_USERINFO_URL', 'https://graph.facebook.com/v18.0/me');

// Build Google authorization URL
function getGoogleAuthUrl() {
    return GOOGLE_AUTH_URL . '?' . http_build_query([
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'state' => 'google'
    ]);
}

// Build Facebook authorization URL
function getFacebookAuthUrl() {
    return FACEBOOK_AUTH_URL . '?' . http_build_query([
        'client_id' => FACEBOOK_APP_ID,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email',
        'state' => 'facebook' // ✅ Thêm state để phân biệt với Google
    ]);
}
?>
