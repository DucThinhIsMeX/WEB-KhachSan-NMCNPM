<?php
// Cấu hình VietQR

// Thông tin ngân hàng (thay bằng thông tin thực tế của khách sạn)
define('VIETQR_BANK_ID', 'VCB'); // Mã ngân hàng (VCB, TCB, MB, ACB, etc.)
define('VIETQR_ACCOUNT_NO', '1234567890'); // Số tài khoản
define('VIETQR_ACCOUNT_NAME', 'KHACH SAN SANG TRONG'); // Tên tài khoản

// Danh sách các ngân hàng hỗ trợ VietQR
$VIETQR_BANKS = [
    'VCB' => 'Vietcombank',
    'TCB' => 'Techcombank',
    'MB' => 'MB Bank',
    'ACB' => 'ACB',
    'VPB' => 'VPBank',
    'TPB' => 'TPBank',
    'STB' => 'Sacombank',
    'HDB' => 'HDBank',
    'BIDV' => 'BIDV',
    'VIB' => 'VIB',
    'SHB' => 'SHB',
    'EIB' => 'Eximbank',
    'MSB' => 'MSB',
    'CAKE' => 'CAKE',
    'Ubank' => 'Ubank',
    'TIMO' => 'Timo',
    'ViettelMoney' => 'ViettelMoney',
    'VNPTMoney' => 'VNPTMoney'
];

/**
 * Generate VietQR URL
 * 
 * @param int $amount Số tiền
 * @param string $description Nội dung chuyển khoản
 * @param string $template Template (compact, compact2, qr_only, print)
 * @return string URL của QR code
 */
function generateVietQRUrl($amount, $description, $template = 'compact2') {
    $bankId = VIETQR_BANK_ID;
    $accountNo = VIETQR_ACCOUNT_NO;
    $accountName = VIETQR_ACCOUNT_NAME;
    
    $params = [
        'amount' => $amount,
        'addInfo' => $description,
        'accountName' => $accountName
    ];
    
    return "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$template}.png?" . http_build_query($params);
}
?>
