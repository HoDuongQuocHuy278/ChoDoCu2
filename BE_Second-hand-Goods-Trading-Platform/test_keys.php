<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$tmnCode = "MUAP6QM1";
$returnUrl = "http://192.168.1.229:8000/api/client/payment/vnpay/callback";
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

$keys = [
    "KEY_1" => "QPMYQVFJSIV5UIIC5RF1U8HKPDLHI21D",
    "KEY_2" => "EU9HDRGVK2XBBEVA6I8E6MQT2F5BWTTU",
    "KEY_3" => "XIWHI99518JWD94QL9737ZS6EHYOWXJL"
];

echo "==================================================\n";
echo "       TEST 3 KEY VNPAY (CLICK ĐỂ KIỂM TRA)\n";
echo "==================================================\n\n";

foreach ($keys as $name => $secret) {
    $vnp_TxnRef = date("YmdHis") . "_" . $name;
    $vnp_Amount = 10000 * 100;
    
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $tmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => "127.0.0.1",
        "vnp_Locale" => "vn",
        "vnp_OrderInfo" => "Test Key " . $name,
        "vnp_OrderType" => "other",
        "vnp_ReturnUrl" => $returnUrl,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes'))
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

    $vnpUrl = $vnp_Url . "?" . $query;
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $secret);
    $vnpUrl .= 'vnp_SecureHash=' . $vnpSecureHash;

    echo "🔹 " . $name . " (" . substr($secret, 0, 5) . "...):\n";
    echo $vnpUrl . "\n\n";
}
echo "==================================================\n";
