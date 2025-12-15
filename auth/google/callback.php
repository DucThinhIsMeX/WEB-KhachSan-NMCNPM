<?php
session_start();
require_once __DIR__ . '/../../controllers/CustomerAuthController.php';

error_log("=== Google Callback Start ===");

if (!isset($_GET['code'])) {
    error_log("No authorization code received");
    if (isset($_GET['error'])) {
        error_log("Google error: " . $_GET['error']);
    }
    header('Location: /customer/login.php?error=google_auth_failed');
    exit;
}

$code = $_GET['code'];
error_log("Authorization code received: " . substr($code, 0, 20) . "...");

$customerAuth = new CustomerAuthController();
$result = $customerAuth->handleGoogleCallback($code);

if ($result) {
    error_log("Google login successful, redirecting to index.php");
    // Redirect về trang chủ với đường dẫn tuyệt đối
    header('Location: /index.php');
    exit;
} else {
    error_log("Google login failed");
    header('Location: /customer/login.php?error=google_login_failed');
    exit;
}
?>
