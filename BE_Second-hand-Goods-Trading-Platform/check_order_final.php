<?php

function callApi($url, $method = 'GET', $data = null) {
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => $method,
            'content' => $data ? json_encode($data) : null,
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true);
}

$orderCode = 'DHOD45BRJY';
$baseUrl = 'http://127.0.0.1:8000/api';

echo "--- 1. CHECKING ORDER STATUS BEFORE ---\n";
$orderBefore = callApi("$baseUrl/debug/order/$orderCode");
if ($orderBefore) {
    echo "ID: " . ($orderBefore['id'] ?? 'N/A') . "\n";
    echo "Status: " . ($orderBefore['status'] ?? 'N/A') . "\n";
    echo "Payment: " . ($orderBefore['payment_status'] ?? 'N/A') . "\n";
} else {
    echo "Order not found or API error.\n";
}

echo "\n--- 2. RUNNING AUTO CHECK (02/12/2025) ---\n";
$checkResult = callApi("$baseUrl/client/payment/auto-check", 'POST', [
    'day_begin' => '02/12/2025',
    'day_end' => '02/12/2025'
]);

if ($checkResult) {
    echo "Status: " . ($checkResult['status'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Message: " . ($checkResult['message'] ?? '') . "\n";
    
    if (!empty($checkResult['data']['updated_orders'])) {
        echo "UPDATED ORDERS:\n";
        print_r($checkResult['data']['updated_orders']);
    } else {
        echo "No orders updated.\n";
    }
    
    if (!empty($checkResult['data']['skipped_orders'])) {
        echo "SKIPPED ORDERS:\n";
        print_r($checkResult['data']['skipped_orders']);
    }
} else {
    echo "Auto check API failed.\n";
}

echo "\n--- 3. CHECKING ORDER STATUS AFTER ---\n";
$orderAfter = callApi("$baseUrl/debug/order/$orderCode");
if ($orderAfter) {
    echo "ID: " . ($orderAfter['id'] ?? 'N/A') . "\n";
    echo "Status: " . ($orderAfter['status'] ?? 'N/A') . "\n";
    echo "Payment: " . ($orderAfter['payment_status'] ?? 'N/A') . "\n";
} else {
    echo "Order not found or API error.\n";
}
