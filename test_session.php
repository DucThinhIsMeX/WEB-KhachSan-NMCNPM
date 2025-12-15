<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Session & OAuth</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
        pre { background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 Session & OAuth Debug</h1>
    
    <div class="box">
        <h2>Session Status</h2>
        <?php if (isset($_SESSION['customer_id'])): ?>
            <p class="success">✅ Logged In</p>
            <pre><?php print_r($_SESSION); ?></pre>
        <?php else: ?>
            <p class="error">❌ Not Logged In</p>
        <?php endif; ?>
    </div>

    <div class="box">
        <h2>OAuth URLs</h2>
        <?php require_once 'config/oauth.php'; ?>
        <p><strong>Google Redirect:</strong> <?= GOOGLE_REDIRECT_URI ?></p>
        <p><strong>Facebook Redirect:</strong> <?= FACEBOOK_REDIRECT_URI ?></p>
    </div>

    <div class="box">
        <h2>Test Links</h2>
        <p><a href="<?= GOOGLE_AUTH_URL ?>">Login with Google</a></p>
        <p><a href="<?= FACEBOOK_AUTH_URL ?>">Login with Facebook</a></p>
        <p><a href="/customer/logout.php">Logout</a></p>
    </div>

    <div class="box">
        <h2>File Paths</h2>
        <pre><?php
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
        echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
        ?></pre>
    </div>
</body>
</html>
