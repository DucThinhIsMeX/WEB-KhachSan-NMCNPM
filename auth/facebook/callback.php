<?php
session_start();
require_once __DIR__ . '/../../controllers/CustomerAuthController.php';

error_log("=== Facebook Callback Start ===");

if (!isset($_GET['code'])) {
    error_log("No authorization code received");
    if (isset($_GET['error'])) {
        error_log("Facebook error: " . $_GET['error']);
        error_log("Error description: " . ($_GET['error_description'] ?? ''));
    }
    header('Location: /customer/login.php?error=facebook_auth_failed');
    exit;
}

$code = $_GET['code'];
error_log("Authorization code received: " . substr($code, 0, 20) . "...");

$customerAuth = new CustomerAuthController();
$result = $customerAuth->handleFacebookCallback($code);

if ($result) {
    error_log("Facebook login successful, redirecting to index.php");
    // Redirect về trang chủ với đường dẫn tuyệt đối
    header('Location: /index.php');
    exit;
} else {
    error_log("Facebook login failed");
    header('Location: /customer/login.php?error=facebook_login_failed');
    exit;
}
?>
