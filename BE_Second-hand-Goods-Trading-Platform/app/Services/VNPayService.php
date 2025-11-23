<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VNPayService
{
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_Url;
    private $vnp_Returnurl;
    private $vnp_apiUrl;

    public function __construct()
    {
        // CHỈ DÙNG SANDBOX (TEST) - KHÔNG DÙNG PRODUCTION
        $this->vnp_TmnCode = env('VNPAY_TMN_CODE', '');
        $this->vnp_HashSecret = env('VNPAY_HASH_SECRET', '');
        
        // FORCE SANDBOX URL - Không cho phép dùng production URL
        $envUrl = env('VNPAY_URL', '');
        if (empty($envUrl) || strpos($envUrl, 'www.vnpayment.vn') !== false) {
            // Nếu không có URL hoặc là production URL, force dùng sandbox
            $this->vnp_Url = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        } else {
            $this->vnp_Url = $envUrl;
        }
        
        $this->vnp_Returnurl = env('VNPAY_RETURN_URL', '');
        
        // FORCE SANDBOX API URL
        $envApiUrl = env('VNPAY_API_URL', '');
        if (empty($envApiUrl) || strpos($envApiUrl, 'www.vnpayment.vn') !== false) {
            // Nếu không có URL hoặc là production URL, force dùng sandbox
            $this->vnp_apiUrl = 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction';
        } else {
            $this->vnp_apiUrl = $envApiUrl;
        }
        
        // Log để debug
        \Log::info('VNPayService Initialized', [
            'url' => $this->vnp_Url,
            'api_url' => $this->vnp_apiUrl,
            'is_sandbox' => strpos($this->vnp_Url, 'sandbox') !== false,
        ]);
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl(array $params): string
    {
        $vnp_TxnRef = $params['txn_ref'] ?? $this->generateTxnRef();
        $vnp_Amount = (int)($params['amount'] * 100); // VNPay yêu cầu số tiền tính bằng xu
        $vnp_OrderInfo = $params['order_info'] ?? 'Thanh toan don hang #' . $vnp_TxnRef;
        $vnp_OrderType = $params['order_type'] ?? 'other';
        $vnp_Locale = $params['locale'] ?? 'vn';
        $vnp_BankCode = $params['bank_code'] ?? '';
        $vnp_IpAddr = $params['ip_address'] ?? request()->ip() ?? '127.0.0.1';
        $vnp_ExpireDate = $params['expire_date'] ?? $this->getExpireDate();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $this->vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
        ];

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

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

        $vnp_Url = $this->vnp_Url . "?" . $query;
        
        if (!empty($this->vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Xác thực chữ ký từ callback VNPay
     */
    public function validateSecureHash(array $inputData, string $vnp_SecureHash): bool
    {
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);
        
        return $secureHash === $vnp_SecureHash;
    }

    /**
     * Lấy thông tin từ callback VNPay
     */
    public function parseCallbackData(array $requestData): array
    {
        $inputData = [];
        foreach ($requestData as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        return [
            'secure_hash' => $inputData['vnp_SecureHash'] ?? '',
            'txn_ref' => $inputData['vnp_TxnRef'] ?? '',
            'amount' => isset($inputData['vnp_Amount']) ? (int)$inputData['vnp_Amount'] / 100 : 0,
            'response_code' => $inputData['vnp_ResponseCode'] ?? '',
            'transaction_no' => $inputData['vnp_TransactionNo'] ?? '',
            'transaction_status' => $inputData['vnp_TransactionStatus'] ?? '',
            'bank_code' => $inputData['vnp_BankCode'] ?? '',
            'card_type' => $inputData['vnp_CardType'] ?? '',
            'pay_date' => $inputData['vnp_PayDate'] ?? '',
            'order_info' => $inputData['vnp_OrderInfo'] ?? '',
            'input_data' => $inputData,
        ];
    }

    /**
     * Kiểm tra thanh toán có thành công không
     */
    public function isPaymentSuccess(string $responseCode, string $transactionStatus): bool
    {
        return $responseCode === '00' && $transactionStatus === '00';
    }

    /**
     * Tạo mã giao dịch tham chiếu
     */
    private function generateTxnRef(): string
    {
        return 'TXN_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Lấy thời gian hết hạn (15 phút từ bây giờ)
     */
    private function getExpireDate(): string
    {
        $startTime = date("YmdHis");
        return date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
    }

    /**
     * Gọi API VNPay (cho refund, query)
     */
    public function callAPI(string $method, string $endpoint, array $data): array
    {
        $url = $this->vnp_apiUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            if (!empty($data)) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $result = curl_exec($ch);
        if (!$result) {
            Log::error('VNPay API Call Failed', [
                'url' => $url,
                'method' => $method,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return ['error' => 'Connection Failure'];
        }

        curl_close($ch);
        return json_decode($result, true) ?? [];
    }
}

