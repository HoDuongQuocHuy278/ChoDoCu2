<?php

$url = 'http://192.168.1.126:8000/api/client/dang-nhap';
$email = 'admin@test.com';
$password = '123456';

echo "Testing Admin Login...\n";
echo "URL: $url\n";
echo "Email: $email\n";

$data = [
    'email' => $email,
    'password' => $password
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "cURL Error: $error\n";
    // Try localhost if IP fails
    echo "Retrying with localhost...\n";
    $url = 'http://127.0.0.1:8000/api/client/dang-nhap';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n";

$json = json_decode($response, true);

if ($httpCode == 200 && isset($json['status']) && $json['status'] == true) {
    echo "\n✅ Login Successful!\n";
    if (isset($json['user_info']['role'])) {
        echo "Role: " . $json['user_info']['role'] . " (Expected: 1)\n";
        if ($json['user_info']['role'] == 1) {
            echo "✅ User is Admin.\n";
        } else {
            echo "❌ User is NOT Admin.\n";
        }
    } else {
        echo "⚠️ Role not found in response.\n";
    }
    echo "Token: " . substr($json['token'], 0, 20) . "...\n";
} else {
    echo "\n❌ Login Failed.\n";
}
