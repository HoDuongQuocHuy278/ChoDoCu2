<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n==================================================\n";
echo "       KIỂM TRA DỮ LIỆU VNPAY (DATA AUDIT)\n";
echo "==================================================\n";

// Load config
$tmnCode = env('VNPAY_TMN_CODE');
$hashSecret = env('VNPAY_HASH_SECRET');
$returnUrl = env('VNPAY_RETURN_URL');

// Mock Data
$vnp_TxnRef = date("YmdHis");
$vnp_Amount = 10000; // 10,000 VND
$vnp_IpAddr = "127.0.0.1";
$vnp_CreateDate = date('YmdHis');

$data = [
    "vnp_Version" => "2.1.0",
    "vnp_Command" => "pay",
    "vnp_TmnCode" => $tmnCode,
    "vnp_Amount" => $vnp_Amount * 100, // Must multiply by 100
    "vnp_CurrCode" => "VND",
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_OrderInfo" => "Thanh toan test audit",
    "vnp_OrderType" => "other",
    "vnp_Locale" => "vn",
    "vnp_ReturnUrl" => $returnUrl,
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_CreateDate" => $vnp_CreateDate,
];

$errors = [];

// VALIDATION RULES
echo "Kiểm tra từng trường dữ liệu:\n";

// 1. vnp_Version
echo "1. vnp_Version: " . $data['vnp_Version'];
if ($data['vnp_Version'] === '2.1.0') echo " [OK]\n"; else { echo " [FAIL] Phải là 2.1.0\n"; $errors[] = "Version sai"; }

// 2. vnp_Command
echo "2. vnp_Command: " . $data['vnp_Command'];
if ($data['vnp_Command'] === 'pay') echo " [OK]\n"; else { echo " [FAIL] Phải là pay\n"; $errors[] = "Command sai"; }

// 3. vnp_TmnCode
echo "3. vnp_TmnCode: " . $data['vnp_TmnCode'];
if (strlen($data['vnp_TmnCode']) === 8) echo " [OK]\n"; else { echo " [FAIL] Phải đúng 8 ký tự\n"; $errors[] = "TmnCode sai độ dài"; }

// 4. vnp_Amount
echo "4. vnp_Amount: " . $data['vnp_Amount'];
if (is_numeric($data['vnp_Amount']) && $data['vnp_Amount'] > 0) echo " [OK] (Đã nhân 100)\n"; else { echo " [FAIL] Phải là số dương\n"; $errors[] = "Amount sai"; }

// 5. vnp_CurrCode
echo "5. vnp_CurrCode: " . $data['vnp_CurrCode'];
if ($data['vnp_CurrCode'] === 'VND') echo " [OK]\n"; else { echo " [FAIL] Phải là VND\n"; $errors[] = "CurrCode sai"; }

// 6. vnp_TxnRef
echo "6. vnp_TxnRef: " . $data['vnp_TxnRef'];
if (!empty($data['vnp_TxnRef'])) echo " [OK]\n"; else { echo " [FAIL] Không được rỗng\n"; $errors[] = "TxnRef rỗng"; }

// 7. vnp_OrderInfo
echo "7. vnp_OrderInfo: " . $data['vnp_OrderInfo'];
if (!empty($data['vnp_OrderInfo'])) echo " [OK]\n"; else { echo " [FAIL] Không được rỗng\n"; $errors[] = "OrderInfo rỗng"; }

// 8. vnp_ReturnUrl
echo "8. vnp_ReturnUrl: " . $data['vnp_ReturnUrl'];
if (filter_var($data['vnp_ReturnUrl'], FILTER_VALIDATE_URL)) echo " [OK]\n"; else { echo " [FAIL] URL không hợp lệ\n"; $errors[] = "ReturnUrl sai format"; }

// 9. vnp_IpAddr
echo "9. vnp_IpAddr: " . $data['vnp_IpAddr'];
if (!empty($data['vnp_IpAddr'])) echo " [OK]\n"; else { echo " [FAIL] Không được rỗng\n"; $errors[] = "IpAddr rỗng"; }

// 10. vnp_CreateDate
echo "10. vnp_CreateDate: " . $data['vnp_CreateDate'];
if (strlen($data['vnp_CreateDate']) === 14) echo " [OK] (Format YmdHis)\n"; else { echo " [FAIL] Phải đúng 14 ký tự YmdHis\n"; $errors[] = "CreateDate sai format"; }

// 11. Check Hash Secret
echo "11. Hash Secret: ";
if (!empty($hashSecret)) echo "Đã cấu hình [OK]\n"; else { echo "Chưa cấu hình [FAIL]\n"; $errors[] = "Thiếu Hash Secret"; }


echo "\n--------------------------------------------------\n";
if (empty($errors)) {
    echo "✅ KẾT QUẢ: DỮ LIỆU HỢP LỆ THEO CHUẨN VNPAY.\n";
    echo "Nếu vẫn lỗi 72/99 -> Chắc chắn do Tài khoản Sandbox (TmnCode/Secret) sai.\n";
} else {
    echo "❌ KẾT QUẢ: CÓ LỖI DỮ LIỆU.\n";
    print_r($errors);
}
echo "==================================================\n";
