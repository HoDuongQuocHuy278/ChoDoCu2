<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DonHang;
use App\Services\VNPayService;

class ThanhToanController extends Controller
{
    /**
     * Tạo thanh toán VNPAY
     */
    public function vnpay_payment(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'nullable|exists:don_hangs,id',
            'order_ids' => 'nullable|array',
            'order_ids.*' => 'exists:don_hangs,id',
            'amount' => 'required|numeric|min:1000',
            'order_info' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:50',
            'locale' => 'nullable|in:vn,en',
        ]);

        try {
            $vnpayService = new VNPayService();

            // Xử lý order_ids (hỗ trợ thanh toán nhiều đơn hàng)
            $orderIds = [];
            if (!empty($data['order_ids']) && is_array($data['order_ids'])) {
                $orderIds = $data['order_ids'];
            } elseif (!empty($data['order_id'])) {
                $orderIds = [$data['order_id']];
            }

            // Tạo mã giao dịch tham chiếu
            if (!empty($orderIds)) {
                if (count($orderIds) > 1) {
                    // Nhiều đơn hàng: dùng format CART
                    $vnp_TxnRef = 'CART_' . time() . '_' . rand(1000, 9999);
                } else {
                    // Một đơn hàng: dùng format ORDER
                    $vnp_TxnRef = 'ORDER_' . $orderIds[0] . '_' . time();
                }
            } else {
                $vnp_TxnRef = 'TXN_' . time() . '_' . rand(1000, 9999);
            }

            // Tạo order_info
            $orderInfo = $data['order_info'];
            if (!$orderInfo) {
                if (count($orderIds) > 1) {
                    $orderInfo = 'Thanh toán ' . count($orderIds) . ' đơn hàng';
                } elseif (!empty($orderIds)) {
                    $order = DonHang::find($orderIds[0]);
                    $orderInfo = 'Thanh toán đơn hàng #' . ($order ? $order->ma_don_hang : $orderIds[0]);
                } else {
                    $orderInfo = 'Thanh toán đơn hàng #' . $vnp_TxnRef;
                }
            }

            // Chuẩn bị thông tin thanh toán
            $paymentParams = [
                'txn_ref' => $vnp_TxnRef,
                'amount' => (float)$data['amount'],
                'order_info' => $orderInfo,
                'order_type' => 'other',
                'locale' => $data['locale'] ?? 'vn',
                'bank_code' => $data['bank_code'] ?? '',
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ];

            // Tạo URL thanh toán
            $paymentUrl = $vnpayService->createPaymentUrl($paymentParams);

            // Cập nhật payment_payload cho tất cả các đơn hàng
            if (!empty($orderIds)) {
                $paymentPayload = [
                    'payment_url' => $paymentUrl,
                    'txn_ref' => $vnp_TxnRef,
                    'amount' => $data['amount'],
                    'order_ids' => $orderIds,
                    'is_cart_payment' => count($orderIds) > 1,
                    'created_at' => now()->toDateTimeString(),
                ];

                foreach ($orderIds as $orderId) {
                    $order = DonHang::find($orderId);
                    if ($order) {
                        $order->payment_payload = json_encode($paymentPayload);
                        $order->save();
                    }
                }
            }

            \Log::info('VNPay Payment URL Created', [
                'order_id' => $data['order_id'] ?? null,
                'txn_ref' => $vnp_TxnRef,
                'amount' => $data['amount'],
            ]);

            return response()->json([
                'status' => true,
                'code' => '00',
                'message' => 'Tạo link thanh toán VNPAY thành công',
                'data' => [
                    'payment_url' => $paymentUrl,
                    'txn_ref' => $vnp_TxnRef,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('VNPay Payment Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tạo link thanh toán: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo thanh toán MBBank
     */
    public function mbbank_payment(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'nullable|exists:don_hangs,id',
            'order_ids' => 'nullable|array',
            'order_ids.*' => 'exists:don_hangs,id',
            'amount' => 'required|numeric|min:1000',
            'order_info' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        try {
            // Xử lý order_ids (hỗ trợ thanh toán nhiều đơn hàng)
            $orderIds = [];
            if (!empty($data['order_ids']) && is_array($data['order_ids'])) {
                $orderIds = $data['order_ids'];
            } elseif (!empty($data['order_id'])) {
                $orderIds = [$data['order_id']];
            }

            // Tạo order_id cho MBBank API
            $mbbankOrderId = '';
            if (!empty($orderIds)) {
                if (count($orderIds) > 1) {
                    $mbbankOrderId = 'CART_' . time() . '_' . rand(1000, 9999);
                } else {
                    $mbbankOrderId = 'ORDER_' . $orderIds[0];
                }
            } else {
                $mbbankOrderId = 'ORDER_' . time() . '_' . rand(1000, 9999);
            }

            // Tạo description
            $description = $data['order_info'];
            if (!$description) {
                if (count($orderIds) > 1) {
                    $description = 'Thanh toán ' . count($orderIds) . ' đơn hàng';
                } elseif (!empty($orderIds)) {
                    $order = DonHang::find($orderIds[0]);
                    $description = 'Thanh toán đơn hàng #' . ($order ? $order->ma_don_hang : $orderIds[0]);
                } else {
                    $description = 'Thanh toán đơn hàng';
                }
            }

            // Gọi API MBBank
            $mbbankApiUrl = 'https://api-mb.midstack.io.vn/api/transactions';

            $payload = [
                'amount' => (int)$data['amount'],
                'description' => $description,
                'order_id' => $mbbankOrderId,
            ];

            // Thêm thông tin khách hàng nếu có
            if (isset($data['customer_name']) && !empty($data['customer_name'])) {
                $payload['customer_name'] = $data['customer_name'];
            }
            if (isset($data['customer_email']) && !empty($data['customer_email'])) {
                $payload['customer_email'] = $data['customer_email'];
            }
            if (isset($data['customer_phone']) && !empty($data['customer_phone'])) {
                $payload['customer_phone'] = $data['customer_phone'];
            }

            // Log để debug
            \Log::info('MBBank Payment Request', [
                'url' => $mbbankApiUrl,
                'payload' => $payload
            ]);

            // Gọi API với headers phù hợp
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($mbbankApiUrl, $payload);

            // Log response để debug
            \Log::info('MBBank Payment Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            // Kiểm tra response
            if (!$response->successful()) {
                $errorBody = $response->body();
                $errorJson = $response->json();

                \Log::error('MBBank Payment Failed', [
                    'status' => $response->status(),
                    'error' => $errorBody,
                    'error_json' => $errorJson,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Không thể tạo giao dịch MBBank. ' . ($errorJson['message'] ?? 'Vui lòng thử lại sau.'),
                    'error' => $errorJson ?? $errorBody,
                    'debug' => [
                        'status_code' => $response->status(),
                        'response_body' => $errorBody,
                    ],
                ], 500);
            }

            $responseData = $response->json();

            // Kiểm tra response có hợp lệ không
            if (!$responseData) {
                \Log::error('MBBank Payment Invalid Response', [
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Phản hồi từ MBBank không hợp lệ',
                    'error' => $response->body(),
                ], 500);
            }

            // Cập nhật payment_payload cho tất cả các đơn hàng
            if (!empty($orderIds)) {
                $paymentPayload = [
                    'payment_data' => $responseData,
                    'order_id' => $mbbankOrderId,
                    'order_ids' => $orderIds,
                    'is_cart_payment' => count($orderIds) > 1,
                    'created_at' => now()->toDateTimeString(),
                ];

                foreach ($orderIds as $orderId) {
                    $order = DonHang::find($orderId);
                    if ($order) {
                        $order->payment_payload = json_encode($paymentPayload);
                        $order->save();
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Tạo giao dịch MBBank thành công',
                'data' => $responseData,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('MBBank Payment Connection Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Không thể kết nối đến MBBank. Vui lòng kiểm tra kết nối mạng.',
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            \Log::error('MBBank Payment Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tạo giao dịch MBBank: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Callback từ VNPAY sau khi thanh toán
     */
    public function vnpayCallback(Request $request)
    {
        \Log::info('VNPAY Callback - Route Hit', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        try {
            $vnpayService = new VNPayService();
            $returnData = [];

            // Log toàn bộ request để debug
            \Log::info('VNPAY Callback - Full Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'all_params' => $request->all(),
            ]);

            // Parse dữ liệu từ callback
            $callbackData = $vnpayService->parseCallbackData($request->all());
            
            // Kiểm tra chữ ký
            $isValidHash = $vnpayService->validateSecureHash(
                $callbackData['input_data'],
                $callbackData['secure_hash']
            );

            \Log::info('VNPAY Callback - Processing', [
                'vnp_TxnRef' => $callbackData['txn_ref'],
                'vnp_ResponseCode' => $callbackData['response_code'],
                'vnp_TransactionStatus' => $callbackData['transaction_status'],
                'hash_match' => $isValidHash,
            ]);

            if ($isValidHash) {
                // Lưu thông tin thanh toán vào payment_payload
                $paymentInfo = [
                    'transaction_no' => $callbackData['transaction_no'],
                    'bank_code' => $callbackData['bank_code'],
                    'card_type' => $callbackData['card_type'],
                    'pay_date' => $callbackData['pay_date'],
                    'amount' => $callbackData['amount'],
                    'response_code' => $callbackData['response_code'],
                    'transaction_status' => $callbackData['transaction_status'],
                    'callback_at' => now()->toDateTimeString(),
                ];

                // Xác định các đơn hàng cần cập nhật
                $ordersToUpdate = [];
                $isCartPayment = false;

                // Kiểm tra xem có phải thanh toán nhiều đơn hàng (CART) không
                if (preg_match('/^CART_/', $callbackData['txn_ref'])) {
                    // Tìm đơn hàng có txn_ref này trong payment_payload
                    $firstOrder = DonHang::where('payment_payload', 'like', '%' . $callbackData['txn_ref'] . '%')->first();
                    if ($firstOrder) {
                        $existingPayload = json_decode($firstOrder->payment_payload, true) ?? [];
                        if (!empty($existingPayload['order_ids']) && is_array($existingPayload['order_ids'])) {
                            $orderIds = $existingPayload['order_ids'];
                            $isCartPayment = true;
                            foreach ($orderIds as $orderId) {
                                $order = DonHang::find($orderId);
                                if ($order) {
                                    $ordersToUpdate[] = $order;
                                }
                            }
                        }
                    }
                } elseif (preg_match('/ORDER_(\d+)_/', $callbackData['txn_ref'], $matches)) {
                    // Thanh toán đơn hàng đơn lẻ
                    $orderId = $matches[1];
                    $order = DonHang::find($orderId);
                    if ($order) {
                        $ordersToUpdate[] = $order;
                    }
                }

                // Kiểm tra thanh toán thành công
                $isPaymentSuccess = $vnpayService->isPaymentSuccess(
                    $callbackData['response_code'],
                    $callbackData['transaction_status']
                );

                if ($isPaymentSuccess && !empty($ordersToUpdate)) {
                    // Cập nhật tất cả các đơn hàng
                    foreach ($ordersToUpdate as $order) {
                        $order->payment_status = 'paid';
                        $order->status = 'processing';

                        // Cập nhật payment_payload
                        $existingPayload = json_decode($order->payment_payload, true) ?? [];
                        $existingPayload['vnpay_callback'] = array_merge($paymentInfo, ['status' => 'success']);
                        $order->payment_payload = json_encode($existingPayload);
                        $order->save();

                        \Log::info('VNPAY Callback - Payment Success', [
                            'order_id' => $order->id,
                            'order_code' => $order->ma_don_hang,
                            'is_cart_payment' => $isCartPayment,
                        ]);

                        // Tạo thông báo cho buyer
                        if ($order->khach_hang_id) {
                            try {
                                \App\Http\Controllers\NotificationController::notifyOrder(
                                    $order->khach_hang_id,
                                    $order->ma_don_hang,
                                    'paid',
                                    "/don-mua"
                                );
                            } catch (\Exception $e) {
                                \Log::error('VNPAY Callback - Failed to notify buyer', [
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }

                        // Tạo thông báo cho seller
                        try {
                            $product = $order->sanPham;
                            if ($product && $product->khach_hang_id) {
                                \App\Http\Controllers\NotificationController::notifyOrder(
                                    $product->khach_hang_id,
                                    $order->ma_don_hang,
                                    'paid',
                                    "/nguoi-ban/quan-ly-don-hang"
                                );
                            }
                        } catch (\Exception $e) {
                            \Log::error('VNPAY Callback - Failed to notify seller', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // Set return data
                    if ($isCartPayment) {
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Thanh toán thành công cho ' . count($ordersToUpdate) . ' đơn hàng';
                        $returnData['order_ids'] = array_map(function($o) { return $o->id; }, $ordersToUpdate);
                        $returnData['order_codes'] = array_map(function($o) { return $o->ma_don_hang; }, $ordersToUpdate);
                    } else {
                        $firstOrder = $ordersToUpdate[0] ?? null;
                        if ($firstOrder) {
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Thanh toán thành công';
                            $returnData['order_id'] = $firstOrder->id;
                            $returnData['order_code'] = $firstOrder->ma_don_hang;
                        }
                    }
                } else {
                    // Thanh toán thất bại - cập nhật tất cả các đơn hàng
                    foreach ($ordersToUpdate as $order) {
                        $order->payment_status = 'failed';

                        // Cập nhật payment_payload
                        $existingPayload = json_decode($order->payment_payload, true) ?? [];
                        $existingPayload['vnpay_callback'] = array_merge($paymentInfo, ['status' => 'failed']);
                        $order->payment_payload = json_encode($existingPayload);
                        $order->save();

                        \Log::warning('VNPAY Callback - Payment Failed', [
                            'order_id' => $order->id,
                            'response_code' => $callbackData['response_code'],
                            'transaction_status' => $callbackData['transaction_status'],
                        ]);
                    }

                    if ($isCartPayment) {
                        $returnData['RspCode'] = $callbackData['response_code'];
                        $returnData['Message'] = 'Thanh toán thất bại cho ' . count($ordersToUpdate) . ' đơn hàng. Mã lỗi: ' . $callbackData['response_code'];
                    } else {
                        $firstOrder = $ordersToUpdate[0] ?? null;
                        if ($firstOrder) {
                            $returnData['RspCode'] = $callbackData['response_code'];
                            $returnData['Message'] = 'Thanh toán thất bại. Mã lỗi: ' . $callbackData['response_code'];
                            $returnData['order_id'] = $firstOrder->id;
                            $returnData['order_code'] = $firstOrder->ma_don_hang;
                        }
                    }
                }

                if (empty($ordersToUpdate)) {
                    \Log::warning('VNPAY Callback - Orders Not Found', [
                        'txn_ref' => $callbackData['txn_ref'],
                    ]);

                    $returnData['RspCode'] = '99';
                    $returnData['Message'] = 'Không tìm thấy đơn hàng';
                }
            } else {
                \Log::error('VNPAY Callback - Invalid Hash', [
                    'received_hash' => $callbackData['secure_hash'],
                ]);

                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Chữ ký không hợp lệ';
            }

            // Lấy frontend URL từ env hoặc từ request origin
            $frontendUrl = env('FRONTEND_URL');

            // Nếu không có FRONTEND_URL trong env, thử lấy từ request
            if (!$frontendUrl) {
                $origin = $request->header('Origin');
                if ($origin) {
                    $frontendUrl = $origin;
                } else {
                    // Fallback
                    $frontendUrl = 'http://localhost:5173';
                }
            }

            // Đảm bảo URL không có trailing slash
            $frontendUrl = rtrim($frontendUrl, '/');

            $redirectUrl = $frontendUrl . '/thong-bao?' . http_build_query($returnData);

            \Log::info('VNPAY Callback - Redirecting', [
                'frontend_url' => $frontendUrl,
                'redirect_url' => $redirectUrl,
                'return_data' => $returnData,
            ]);

            // Hiển thị trang callback với thông tin chi tiết trước khi redirect
            return view('vnpay_callback', [
                'status' => $returnData['RspCode'] == '00' ? 'success' : 'failed',
                'message' => $returnData['Message'] ?? '',
                'order_code' => $returnData['order_code'] ?? null,
                'vnp_TransactionNo' => $callbackData['transaction_no'],
                'vnp_Amount' => $callbackData['amount'],
                'vnp_BankCode' => $callbackData['bank_code'],
                'vnp_CardType' => $callbackData['card_type'],
                'vnp_PayDate' => $callbackData['pay_date'],
                'vnp_ResponseCode' => $callbackData['response_code'],
                'redirect_url' => $redirectUrl,
                'debug' => config('app.debug') ? [
                    'return_data' => $returnData,
                    'frontend_url' => $frontendUrl,
                ] : null,
            ]);

        } catch (\Exception $e) {
            \Log::error('VNPAY Callback - Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // Vẫn hiển thị trang callback với thông báo lỗi
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $frontendUrl = rtrim($frontendUrl, '/');

            $returnData = [
                'RspCode' => '99',
                'Message' => 'Lỗi xử lý callback: ' . $e->getMessage(),
            ];

            $redirectUrl = $frontendUrl . '/payment/callback?' . http_build_query($returnData);

            return view('vnpay_callback', [
                'status' => 'failed',
                'message' => $returnData['Message'],
                'redirect_url' => $redirectUrl,
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ] : null,
            ]);
        }
    }

    /**
     * Test MBBank API (chỉ dùng để debug)
     */
    public function testMbbankApi(Request $request)
    {
        try {
            $testPayload = [
                'amount' => 10000,
                'description' => 'Test payment',
                'order_id' => 'TEST_' . time(),
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api-mb.midstack.io.vn/api/transactions', $testPayload);

            return response()->json([
                'status' => $response->successful(),
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'json' => $response->json(),
                'request_payload' => $testPayload,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Hoàn tiền giao dịch VNPay (Refund)
     */
    public function vnpayRefund(Request $request)
    {
        $data = $request->validate([
            'txn_ref' => 'required|string',
            'transaction_date' => 'required|string|regex:/^\d{14}$/', // yyyyMMddHHmmss
            'amount' => 'required|numeric|min:1000',
            'transaction_type' => 'nullable|in:02,03', // 02: hoàn toàn phần, 03: hoàn một phần
            'transaction_no' => 'nullable|string',
            'order_info' => 'nullable|string|max:255',
            'create_by' => 'nullable|string|max:255',
        ]);

        try {
            $vnpayService = new VNPayService();

            $refundParams = [
                'txn_ref' => $data['txn_ref'],
                'transaction_date' => $data['transaction_date'],
                'amount' => (float)$data['amount'],
                'transaction_type' => $data['transaction_type'] ?? '02',
                'transaction_no' => $data['transaction_no'] ?? '0',
                'order_info' => $data['order_info'] ?? 'Hoan tien giao dich',
                'create_by' => $data['create_by'] ?? 'admin',
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ];

            $result = $vnpayService->refund($refundParams);

            \Log::info('VNPay Refund Result', [
                'txn_ref' => $data['txn_ref'],
                'result' => $result,
            ]);

            // Kiểm tra kết quả
            if (isset($result['vnp_ResponseCode']) && $result['vnp_ResponseCode'] == '00') {
                // Hoàn tiền thành công
                // Cập nhật đơn hàng nếu có
                if (preg_match('/ORDER_(\d+)_/', $data['txn_ref'], $matches)) {
                    $orderId = $matches[1];
                    $order = DonHang::find($orderId);
                    if ($order) {
                        $existingPayload = json_decode($order->payment_payload, true) ?? [];
                        $existingPayload['refund'] = [
                            'refund_result' => $result,
                            'refunded_at' => now()->toDateTimeString(),
                            'refund_amount' => $data['amount'],
                        ];
                        $order->payment_payload = json_encode($existingPayload);
                        $order->payment_status = 'refunded';
                        $order->save();
                    }
                }

                return response()->json([
                    'status' => true,
                    'code' => '00',
                    'message' => 'Hoàn tiền thành công',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'code' => $result['vnp_ResponseCode'] ?? '99',
                    'message' => $result['vnp_Message'] ?? 'Hoàn tiền thất bại',
                    'data' => $result,
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('VNPay Refund Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi hoàn tiền: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tra cứu giao dịch VNPay (Query Transaction)
     */
    public function vnpayQuery(Request $request)
    {
        $data = $request->validate([
            'txn_ref' => 'required|string',
            'transaction_date' => 'required|string|regex:/^\d{14}$/', // yyyyMMddHHmmss
            'order_info' => 'nullable|string|max:255',
        ]);

        try {
            $vnpayService = new VNPayService();

            $queryParams = [
                'txn_ref' => $data['txn_ref'],
                'transaction_date' => $data['transaction_date'],
                'order_info' => $data['order_info'] ?? 'Query transaction',
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ];

            $result = $vnpayService->queryTransaction($queryParams);

            \Log::info('VNPay Query Result', [
                'txn_ref' => $data['txn_ref'],
                'result' => $result,
            ]);

            // Kiểm tra kết quả
            if (isset($result['vnp_ResponseCode']) && $result['vnp_ResponseCode'] == '00') {
                return response()->json([
                    'status' => true,
                    'code' => '00',
                    'message' => 'Tra cứu thành công',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'code' => $result['vnp_ResponseCode'] ?? '99',
                    'message' => $result['vnp_Message'] ?? 'Tra cứu thất bại',
                    'data' => $result,
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('VNPay Query Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tra cứu: ' . $e->getMessage(),
            ], 500);
        }
    }
}
