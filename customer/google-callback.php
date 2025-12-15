<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';

// Debug: Log toàn bộ request
error_log("=== Google Callback Start ===");
error_log("GET params: " . print_r($_GET, true));
error_log("Session before: " . print_r($_SESSION, true));

$auth = new CustomerAuthController();

// Xử lý lỗi từ Google
if (isset($_GET['error'])) {
    $errorMsg = 'Đăng nhập bị từ chối: ' . $_GET['error'];
    if (isset($_GET['error_description'])) {
        $errorMsg .= ' - ' . $_GET['error_description'];
    }
    $_SESSION['error'] = $errorMsg;
    error_log("Google OAuth Error: " . $errorMsg);
    header('Location: login.php');
    exit;
}

// Xử lý code từ Google
if (isset($_GET['code'])) {
    try {
        error_log("Received authorization code, attempting to exchange for token...");
        
        $userId = $auth->handleGoogleCallback($_GET['code']);
        
        error_log("handleGoogleCallback returned: " . ($userId ? "User ID $userId" : "false"));
        error_log("Session after: " . print_r($_SESSION, true));
        
        if ($userId) {
            $_SESSION['success'] = 'Đăng nhập thành công! Chào mừng bạn đến với hệ thống.';
            error_log("Login successful, redirecting to index.php");
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Không thể lấy thông tin từ Google. Vui lòng kiểm tra logs để biết chi tiết.';
            error_log("Login failed - handleGoogleCallback returned false");
            header('Location: login.php');
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = 'Lỗi hệ thống: ' . $e->getMessage();
        $_SESSION['error'] = $errorMsg;
        error_log("Exception in google-callback: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        header('Location: login.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'Không nhận được mã xác thực từ Google.';
    error_log("No authorization code received");
    header('Location: login.php');
    exit;
}
