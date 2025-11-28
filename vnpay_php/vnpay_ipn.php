<?php
require_once("./config.php");

date_default_timezone_set('Asia/Ho_Chi_Minh');

$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

// Lấy secure hash
$vnp_SecureHash = $inputData['vnp_SecureHash'];
unset($inputData['vnp_SecureHash']);

// Sắp xếp params đúng thứ tự
ksort($inputData);
$hashData = urldecode(http_build_query($inputData));

// Tạo secure hash
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// Nếu chữ ký hợp lệ
if ($secureHash === $vnp_SecureHash) {
    
    $orderId = $inputData['vnp_TxnRef'];
    $vnp_Amount = $inputData['vnp_Amount'] / 100;
    $vnp_ResponseCode = $inputData['vnp_ResponseCode'];
    $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'];

    // TODO: Lấy đơn hàng từ database
    // $order = getOrderFromDB($orderId);

    $order = [
        "Amount" => $vnp_Amount,
        "Status" => 0
    ];

    if ($order) {

        if ($order["Amount"] != $vnp_Amount) {
            $returnData['RspCode'] = '04';
            $returnData['Message'] = 'Invalid amount';
        } else {
            if ($order["Status"] == 0) {

                if ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
                    $orderStatus = 1; // Success
                } else {
                    $orderStatus = 2; // Failed
                }

                // TODO: cập nhật vào DB

                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm success';

            } else {
                $returnData['RspCode'] = '02';
                $returnData['Message'] = 'Order already confirmed';
            }
        }

    } else {
        $returnData['RspCode'] = '01';
        $returnData['Message'] = 'Order not found';
    }

} else {
    $returnData['RspCode'] = '97';
    $returnData['Message'] = 'Invalid signature';
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($returnData);
