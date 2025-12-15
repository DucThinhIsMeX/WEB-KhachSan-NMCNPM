<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';

$auth = new CustomerAuthController();

// Check for errors
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Đăng nhập thất bại: ' . $_GET['error'];
    header('Location: login.php');
    exit;
}

// Check for code
if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'Không nhận được mã xác thực';
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];
$state = $_GET['state'] ?? '';

// Handle based on state
if ($state === 'google') {
    $userId = $auth->handleGoogleCallback($code);
} else if ($state === 'facebook') {
    $userId = $auth->handleFacebookCallback($code);
} else {
    // Try Google first, then Facebook
    $userId = $auth->handleGoogleCallback($code);
    if (!$userId) {
        $userId = $auth->handleFacebookCallback($code);
    }
}

if ($userId) {
    header('Location: ../index.php');
} else {
    $_SESSION['error'] = 'Đăng nhập thất bại. Vui lòng thử lại.';
    header('Location: login.php');
}
exit;
?>
