<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

echo "==================================================\n";
echo "       TEST KẾT NỐI MBBANK API (MIDSTACK)\n";
echo "==================================================\n";

$url = 'https://api-mb.midstack.io.vn/api/transactions';
$payload = [
    'amount' => 10000,
    'description' => 'Test connection from script',
    'order_id' => 'TEST_SCRIPT_' . time(),
    'customer_name' => 'Test User',
    'customer_email' => 'test@example.com',
    'customer_phone' => '0901234567',
    'returnUrl' => 'http://localhost:8000/api/client/payment/mbbank/callback',
    'cancelUrl' => 'http://localhost:8000/api/client/payment/mbbank/cancel',
];

echo "URL: $url\n";
echo "Payload: " . json_encode($payload) . "\n\n";
echo "Sending request...\n";

try {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Disable SSL verification for testing if needed (use with caution)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    
    $logContent = "HTTP Code: $httpCode\n";
    if ($error) {
        echo "❌ cURL Error: $error\n";
        $logContent .= "Error: $error\n";
    } else {
        echo "Response Body:\n$response\n";
        $logContent .= "Response: $response\n";
        
        $json = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "✅ Request Successful\n";
        } else {
            echo "❌ Request Failed\n";
        }
    }
    file_put_contents('mbbank_debug.log', $logContent);

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    file_put_contents('mbbank_debug.log', "Exception: " . $e->getMessage());
}

echo "==================================================\n";
