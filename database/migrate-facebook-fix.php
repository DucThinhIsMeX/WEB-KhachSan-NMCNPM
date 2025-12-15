<?php
/**
 * Migration: Fix KHACHHANG_USERS table để hỗ trợ Facebook OAuth
 * Chạy file này nếu gặp lỗi UNIQUE constraint với Email
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Migration: Fix KHACHHANG_USERS for Facebook OAuth ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();
    
    // Check current table structure
    echo "1. Checking current table structure...\n";
    $stmt = $db->query("PRAGMA table_info(KHACHHANG_USERS)");
    $columns = $stmt->fetchAll();
    
    echo "   Current columns:\n";
    foreach ($columns as $col) {
        echo "   - {$col['name']} ({$col['type']})\n";
    }
    
    // Backup data
    echo "\n2. Backing up existing data...\n";
    $stmt = $db->query("SELECT * FROM KHACHHANG_USERS");
    $existingUsers = $stmt->fetchAll();
    echo "   Found " . count($existingUsers) . " users\n";
    
    // Drop and recreate table
    echo "\n3. Recreating table without Email UNIQUE constraint...\n";
    
    $db->exec("DROP TABLE IF EXISTS KHACHHANG_USERS_BACKUP");
    $db->exec("ALTER TABLE KHACHHANG_USERS RENAME TO KHACHHANG_USERS_BACKUP");
    
    // Create new table structure
    $db->exec("CREATE TABLE KHACHHANG_USERS (
        MaKhachHangUser INTEGER PRIMARY KEY AUTOINCREMENT,
        Email TEXT NOT NULL,
        TenHienThi TEXT NOT NULL,
        Avatar TEXT,
        Provider TEXT NOT NULL CHECK(Provider IN ('google', 'facebook')),
        ProviderId TEXT NOT NULL,
        AccessToken TEXT,
        RefreshToken TEXT,
        NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
        LanDangNhapCuoi DATETIME,
        TrangThai TEXT DEFAULT 'Hoạt động' CHECK(TrangThai IN ('Hoạt động', 'Khóa')),
        UNIQUE(Provider, ProviderId)
    )");
    
    echo "   ✓ New table created\n";
    
    // Restore data with fixes
    echo "\n4. Restoring data...\n";
    if (count($existingUsers) > 0) {
        $stmt = $db->prepare("INSERT INTO KHACHHANG_USERS 
            (MaKhachHangUser, Email, TenHienThi, Avatar, Provider, ProviderId, AccessToken, RefreshToken, NgayTao, LanDangNhapCuoi, TrangThai)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($existingUsers as $user) {
            // Fix email nếu là Facebook user và email trùng
            $email = $user['Email'];
            if ($user['Provider'] === 'facebook' && strpos($email, '@facebook.user') !== false) {
                // Ensure unique email
                $email = 'fb_' . $user['ProviderId'] . '_' . time() . '@facebook.user';
            }
            
            $stmt->execute([
                $user['MaKhachHangUser'],
                $email,
                $user['TenHienThi'],
                $user['Avatar'],
                $user['Provider'],
                $user['ProviderId'],
                $user['AccessToken'],
                $user['RefreshToken'] ?? null,
                $user['NgayTao'],
                $user['LanDangNhapCuoi'],
                $user['TrangThai']
            ]);
        }
        
        echo "   ✓ Restored " . count($existingUsers) . " users\n";
    }
    
    // Drop backup table
    echo "\n5. Cleaning up...\n";
    $db->exec("DROP TABLE KHACHHANG_USERS_BACKUP");
    echo "   ✓ Backup table removed\n";
    
    // Verify
    echo "\n6. Verifying...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM KHACHHANG_USERS");
    $count = $stmt->fetch()['count'];
    echo "   ✓ Final user count: $count\n";
    
    echo "\n=== Migration completed successfully! ===\n";
    echo "\nYou can now use Facebook OAuth without Email UNIQUE constraint issues.\n";
    
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
