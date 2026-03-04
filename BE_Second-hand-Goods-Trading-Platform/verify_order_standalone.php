<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$orderCode = 'DHOD45BRJY';
echo "Checking Order: $orderCode\n";

$order = \App\Models\DonHang::where('ma_don_hang', $orderCode)->first();

if ($order) {
    echo "Order Found!\n";
    echo "ID: " . $order->id . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Payment Status: " . $order->payment_status . "\n";
    echo "Total: " . number_format($order->tong_tien) . "\n";
} else {
    echo "Order Not Found!\n";
}

echo "\nRunning Auto Payment Check...\n";
$req = new \Illuminate\Http\Request();
$req->merge(['day_begin' => '02/12/2025', 'day_end' => '02/12/2025']);
$controller = new \App\Http\Controllers\DonHangController();

try {
    $res = $controller->autoCheckPayment($req);
    $data = $res->getData(true);
    
    echo "API Status: " . ($data['status'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Message: " . $data['message'] . "\n";
    
    if (!empty($data['data']['updated_orders'])) {
        echo "Updated Orders:\n";
        print_r($data['data']['updated_orders']);
    } else {
        echo "No orders updated.\n";
    }
    
    if (!empty($data['data']['skipped_orders'])) {
        echo "Skipped Orders:\n";
        print_r($data['data']['skipped_orders']);
    }
    
    echo "Total Transactions: " . ($data['data']['total_transactions'] ?? 0) . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
