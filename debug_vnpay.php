<?php
// Debug VNPay signature generation

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$bookingCode = 'TEST' . rand(1000, 9999);
$totalAmount = 100000;
$bankCode = 'NCB';

$vnp_TmnCode = env('VNP_TMN_CODE');
$vnp_HashSecret = env('VNP_HASH_SECRET');
$vnp_Url = env('VNP_SANDBOX_URL', env('VNP_URL'));
$vnp_Returnurl = env('VNP_SANDBOX_RETURN_URL', env('VNP_RETURN_URL'));

echo "=== VNPay Debug ===\n";
echo "TMN_CODE: " . $vnp_TmnCode . "\n";
echo "HASH_SECRET: " . $vnp_HashSecret . "\n";
echo "Return URL: " . $vnp_Returnurl . "\n\n";

$inputData = [
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $totalAmount * 100,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => "127.0.0.1",
    "vnp_Locale" => "vi",
    "vnp_OrderInfo" => "Thanh toan ve xem phim FilmGo - Ma HD: " . $bookingCode,
    "vnp_OrderType" => "billpayment",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $bookingCode,
    "vnp_SecureHashType" => "SHA512",
    "vnp_BankCode" => $bankCode,
];

ksort($inputData);

echo "Input Data (sorted):\n";
foreach ($inputData as $key => $value) {
    echo "  $key: $value\n";
}

$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$query = rtrim($query, '&');
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

$fullUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnpSecureHash;

echo "\n=== Hash Data (for signature) ===\n";
echo $hashdata . "\n";

echo "\n=== Secure Hash ===\n";
echo $vnpSecureHash . "\n";

echo "\n=== Full URL ===\n";
echo $fullUrl . "\n";
