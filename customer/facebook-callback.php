<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';

$auth = new CustomerAuthController();

if (isset($_GET['code'])) {
    $userId = $auth->handleFacebookCallback($_GET['code']);
    
    if ($userId) {
        // Đăng nhập thành công
        $_SESSION['success'] = 'Đăng nhập thành công!';
        header('Location: ../index.php');
        exit;
    } else {
        // Đăng nhập thất bại
        $_SESSION['error'] = 'Đăng nhập thất bại. Vui lòng thử lại.';
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
