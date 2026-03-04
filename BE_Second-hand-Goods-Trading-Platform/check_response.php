<?php
$json = file_get_contents('response.json');
$data = json_decode($json, true);
if (!$data) { echo "JSON Decode Error\n"; exit; }
echo "Total Transactions: " . ($data['data']['total_transactions'] ?? 0) . "\n";
echo "Updated Orders:\n";
print_r($data['data']['updated_orders'] ?? []);
echo "Skipped Orders:\n";
print_r($data['data']['skipped_orders'] ?? []);
