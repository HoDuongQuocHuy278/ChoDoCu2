$order = App\Models\DonHang::where('ma_don_hang', 'DHOD45BRJY')->first();
if($order) {
    echo "--------------------------------------------------\n";
    echo "ORDER FOUND:\n";
    echo "ID: " . $order->id . "\n";
    echo "Code: " . $order->ma_don_hang . "\n";
    echo "Total: " . number_format($order->tong_tien) . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Payment: " . $order->payment_status . "\n";
    echo "--------------------------------------------------\n";
} else {
    echo "--------------------------------------------------\n";
    echo "ORDER NOT FOUND: DHOD45BRJY\n";
    echo "--------------------------------------------------\n";
}

echo "RUNNING AUTO CHECK...\n";
$req = new Illuminate\Http\Request();
$req->merge(['day_begin' => '02/12/2025', 'day_end' => '02/12/2025']);
$controller = new App\Http\Controllers\DonHangController();
try {
    $res = $controller->autoCheckPayment($req);
    $data = $res->getData(true);
    print_r($data);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
