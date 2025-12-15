<?php
session_start();
require_once __DIR__ . '/../controllers/CustomerAuthController.php';

$auth = new CustomerAuthController();
$auth->logout();

$_SESSION['success'] = 'Đăng xuất thành công!';
header('Location: ../index.php');
exit;
