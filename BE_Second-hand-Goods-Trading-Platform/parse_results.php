<?php
function readJson($file) {
    if (!file_exists($file)) return null;
    $content = file_get_contents($file);
    // Curl might output headers if -i is used, or if it's a raw response. 
    // But here we expect JSON body. If there are headers, json_decode will fail.
    // Let's try to find the JSON part if needed, but usually curl > file saves body.
    return json_decode($content, true);
}

$o1 = readJson('order_status_1.json');
$check = readJson('check_result.json');
$o2 = readJson('order_status_2.json');

echo "--- ORDER BEFORE ---\n";
if ($o1) {
    echo "ID: " . ($o1['id'] ?? 'N/A') . "\n";
    echo "Status: " . ($o1['status'] ?? 'N/A') . "\n";
    echo "Payment: " . ($o1['payment_status'] ?? 'N/A') . "\n";
} else {
    echo "Failed to read order_status_1.json\n";
}

echo "\n--- CHECK RESULT ---\n";
if ($check) {
    echo "Status: " . ($check['status'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Message: " . ($check['message'] ?? '') . "\n";
    echo "Updated Orders: " . count($check['data']['updated_orders'] ?? []) . "\n";
    if (!empty($check['data']['updated_orders'])) {
        print_r($check['data']['updated_orders']);
    }
    echo "Skipped Orders: " . count($check['data']['skipped_orders'] ?? []) . "\n";
    if (!empty($check['data']['skipped_orders'])) {
        print_r($check['data']['skipped_orders']);
    }
} else {
    echo "Failed to read check_result.json\n";
}

echo "\n--- ORDER AFTER ---\n";
if ($o2) {
    echo "ID: " . ($o2['id'] ?? 'N/A') . "\n";
    echo "Status: " . ($o2['status'] ?? 'N/A') . "\n";
    echo "Payment: " . ($o2['payment_status'] ?? 'N/A') . "\n";
} else {
    echo "Failed to read order_status_2.json\n";
}
