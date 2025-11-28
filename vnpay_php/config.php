<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TmnCode = "MUAP6QM1"; // Mã Merchant Sandbox
$vnp_HashSecret = "QPMYQVFJSIV5UIIC5RF1U8HKPDLHI21D"; // Secret Key Sandbox

// URL thanh toán dành cho SANDBOX
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

// URL Callback phải TRÙNG KHỚP với backend Laravel
$vnp_Returnurl = "http://localhost:8000/api/client/payment/vnpay/callback";

// API Query / Refund chuẩn SANDBOX
$vnp_apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";

// Thời gian hết hạn thanh toán (15 phút)
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
