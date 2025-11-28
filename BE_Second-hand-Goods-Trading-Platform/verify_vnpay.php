<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n==================================================\n";
echo "       KIỂM TRA CẤU HÌNH VNPAY (VNPAY CHECK)\n";
echo "==================================================\n";

// 1. Kiểm tra Múi giờ
$timezone = config('app.timezone');
echo "[1] Múi giờ (Timezone): " . $timezone . "\n";
if ($timezone !== 'Asia/Ho_Chi_Minh') {
    echo "    ❌ LỖI: Múi giờ phải là 'Asia/Ho_Chi_Minh'.\n";
} else {
    echo "    ✅ OK\n";
}

// 2. Kiểm tra IP/URL
$appUrl = env('APP_URL');
$vnpReturnUrl = env('VNPAY_RETURN_URL');
echo "\n[2] Cấu hình URL:\n";
echo "    APP_URL: " . $appUrl . "\n";
echo "    VNPAY_RETURN_URL: " . $vnpReturnUrl . "\n";

if (strpos($vnpReturnUrl, 'localhost') !== false || strpos($vnpReturnUrl, '127.0.0.1') !== false) {
    echo "    ⚠️ CẢNH BÁO: Return URL là localhost, có thể không nhận được callback nếu test từ máy khác.\n";
} else {
    echo "    ✅ OK (Dùng IP mạng)\n";
}

// 3. Kiểm tra Credentials
$tmnCode = env('VNPAY_TMN_CODE');
$hashSecret = env('VNPAY_HASH_SECRET');
echo "\n[3] Thông tin kết nối (Credentials):\n";
echo "    Terminal ID (TmnCode): " . $tmnCode . "\n";
echo "    Secret Key: " . substr($hashSecret, 0, 5) . "..." . substr($hashSecret, -5) . " (Đã ẩn)\n";
echo "    Độ dài Secret Key: " . strlen($hashSecret) . " ký tự\n";

if (strlen($hashSecret) < 20) {
    echo "    ❌ LỖI: Secret Key quá ngắn, có thể sai.\n";
}

// 4. Tạo Test URL
echo "\n[4] Tạo Link Thanh Toán Test:\n";

$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_TxnRef = date("YmdHis");
$vnp_Amount = 10000 * 100; // 10,000 VND

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $tmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => "127.0.0.1",
    "vnp_Locale" => "vn",
    "vnp_OrderInfo" => "Test VNPAY " . $vnp_TxnRef,
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnpReturnUrl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes'))
);

ksort($inputData);
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

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($hashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $hashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

echo "    Link: " . $vnp_Url . "\n";
echo "\n==================================================\n";
echo "HƯỚNG DẪN:\n";
echo "1. Copy link trên và chạy trên trình duyệt.\n";
echo "2. Nếu gặp lỗi 'Code 72': Terminal ID '$tmnCode' KHÔNG TỒN TẠI hoặc BỊ KHÓA.\n";
echo "   -> Giải pháp: Đăng ký tài khoản Sandbox mới để lấy Terminal ID khác.\n";
echo "3. Nếu gặp lỗi 'Code 99': Secret Key sai hoặc Lỗi chữ ký.\n";
echo "==================================================\n";
