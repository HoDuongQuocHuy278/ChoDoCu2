<?php

// Cấu hình từ .env
$vnp_TmnCode = "MUAP6QM1"; // Terminal ID
$vnp_HashSecret = "XIWHI99518JWD94QL9737ZS6EHYOWXJL"; // Secret Key
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://192.168.1.61:8000/api/client/payment/vnpay/callback";

$vnp_TxnRef = date("YmdHis"); // Mã đơn hàng
$vnp_OrderInfo = "Thanh toan test";
$vnp_OrderType = "other";
$vnp_Amount = 10000 * 100; // 10,000 VND
$vnp_Locale = "vn";
$vnp_IpAddr = "127.0.0.1";
$vnp_CreateDate = date('YmdHis');
$vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($vnp_CreateDate)));

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => $vnp_CreateDate,
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $vnp_ExpireDate
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
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

echo "--------------------------------------------------\n";
echo "TEST VNPAY URL GENERATION\n";
echo "--------------------------------------------------\n";
echo "Terminal ID: " . $vnp_TmnCode . "\n";
echo "Secret Key: " . $vnp_HashSecret . "\n";
echo "Create Date: " . $vnp_CreateDate . "\n";
echo "Expire Date: " . $vnp_ExpireDate . "\n";
echo "Hash Data: " . $hashdata . "\n";
echo "Secure Hash: " . $vnpSecureHash . "\n";
echo "--------------------------------------------------\n";
echo "GENERATED URL:\n";
echo $vnp_Url . "\n";
echo "--------------------------------------------------\n";
