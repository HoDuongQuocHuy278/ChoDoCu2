<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "--- CHECKING SPECIFIC ORDER ---\n";
$order = \App\Models\DonHang::where('ma_don_hang', 'DHOD45BRJY')->first();
if ($order) {
    echo "Found Order: " . $order->ma_don_hang . "\n";
    echo "ID: " . $order->id . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Payment Status: " . $order->payment_status . "\n";
    echo "Total: " . number_format($order->tong_tien) . "\n";
    echo "Created: " . $order->created_at . "\n";
} else {
    echo "Order DHOD45BRJY NOT FOUND.\n";
}

echo "\n--- ORDERS TODAY (2025-12-02) ---\n";
$orders = \App\Models\DonHang::whereDate('created_at', '2025-12-02')->get();
if ($orders->isEmpty()) {
    echo "No orders found for today.\n";
} else {
    foreach ($orders as $o) {
        echo "- [{$o->id}] {$o->ma_don_hang} | " . number_format($o->tong_tien) . " | {$o->payment_status} | {$o->status}\n";
    }
}

echo "\n--- RUNNING AUTO CHECK PAYMENT ---\n";
$req = new \Illuminate\Http\Request();
$req->merge(['day_begin' => '02/12/2025', 'day_end' => '02/12/2025']);
$controller = new \App\Http\Controllers\DonHangController();

try {
    $res = $controller->autoCheckPayment($req);
    $data = $res->getData(true);
    
    echo "API Status: " . ($data['status'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Message: " . $data['message'] . "\n";
    
    if (isset($data['data']['updated_orders']) && !empty($data['data']['updated_orders'])) {
        echo "UPDATED ORDERS:\n";
        print_r($data['data']['updated_orders']);
    } else {
        echo "No orders updated.\n";
    }
    
    if (isset($data['data']['skipped_orders']) && !empty($data['data']['skipped_orders'])) {
        echo "SKIPPED ORDERS:\n";
        print_r($data['data']['skipped_orders']);
    }
    
    echo "Total Transactions Fetched: " . ($data['data']['total_transactions'] ?? 0) . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
