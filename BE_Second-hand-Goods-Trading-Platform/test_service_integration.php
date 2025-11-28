<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\VNPayService;

echo "==================================================\n";
echo "       TEST VNPAY SERVICE INTEGRATION\n";
echo "==================================================\n";

try {
    $vnpayService = new VNPayService();
    
    $params = [
        'amount' => 10000,
        'order_info' => 'Test Service Integration',
        'order_type' => 'other',
        'txn_ref' => date("YmdHis") . "_SERVICE",
    ];

    $url = $vnpayService->createPaymentUrl($params);

    echo "Generated URL:\n";
    echo $url . "\n";
    
    if (strpos($url, '&&vnp_SecureHash') !== false) {
        echo "\n❌ LỖI: URL chứa '&&vnp_SecureHash' (Double Ampersand)\n";
    } else {
        echo "\n✅ OK: URL format chuẩn.\n";
    }

} catch (Exception $e) {
    echo "❌ LỖI EXCEPTION: " . $e->getMessage() . "\n";
}
echo "==================================================\n";
