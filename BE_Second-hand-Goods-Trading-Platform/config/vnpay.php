<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VNPay Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình thông tin kết nối VNPay
    | Lấy thông tin từ: https://sandbox.vnpayment.vn/ (cho test)
    |
    */

    // Mã định danh merchant (Terminal Code)
    'tmn_code' => env('VNPAY_TMN_CODE', ''),

    // Secret key để tạo chữ ký
    'hash_secret' => env('VNPAY_HASH_SECRET', ''),

    // URL thanh toán VNPay - CHỈ DÙNG SANDBOX (TEST)
    // KHÔNG ĐỔI THÀNH PRODUCTION URL (www.vnpayment.vn)
    'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),

    // URL trả về sau thanh toán
    'return_url' => env('VNPAY_RETURN_URL', ''),

    // API URL VNPay - CHỈ DÙNG SANDBOX (TEST)
    // KHÔNG ĐỔI THÀNH PRODUCTION URL (www.vnpayment.vn)
    'api_url' => env('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),

    // Version API
    'version' => '2.1.0',

    // Currency
    'currency' => 'VND',

    // Locale mặc định
    'locale' => 'vn',

    // Thời gian hết hạn giao dịch (phút)
    'expire_minutes' => 15,
];

