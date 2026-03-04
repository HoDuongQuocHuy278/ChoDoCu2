<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonHangController extends Controller
{
    /**
     * Tạo đơn hàng theo luồng "Mua ngay"
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:san_phams,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'payment_method' => 'required|in:vnpay,mbbank,momo,cash',
            'buyer_name' => 'required|string|max:120',
            'buyer_email' => 'nullable|email|max:150',
            'buyer_phone' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = SanPham::findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;
        $total = (float) $product->gia * $quantity;

        $authUser = $request->user('sanctum');

        $order = DonHang::create([
            'ma_don_hang' => $this->generateOrderCode(),
            'san_pham_id' => $product->id,
            'khach_hang_id' => $authUser?->id,
            'so_luong' => $quantity,
            'tong_tien' => $total,
            'buyer_name' => $data['buyer_name'],
            'buyer_email' => $data['buyer_email'] ?? null,
            'buyer_phone' => $data['buyer_phone'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_method'] === 'cash' ? 'pending' : 'awaiting_payment',
            'status' => 'pending',
            'payment_payload' => null,
        ]);

        $order->load('sanPham');

        // Tăng số lượt mua của sản phẩm
        $product->increment('so_luot_mua', $quantity);

        // Tạo thông báo cho buyer
        if ($authUser) {
            \App\Http\Controllers\NotificationController::notifyOrder(
                $authUser->id,
                $order->ma_don_hang,
                'created',
                "/don-mua"
            );
        }

        // Tạo thông báo cho seller
        if ($product->khach_hang_id) {
            \App\Http\Controllers\NotificationController::notifyOrder(
                $product->khach_hang_id,
                $order->ma_don_hang,
                'created',
                "/nguoi-ban/quan-ly-don-hang"
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Tạo đơn hàng thành công',
            'data' => $order,
        ], 201);
    }

    /**
     * Chi tiết đơn hàng
     */
    public function show(DonHang $donHang)
    {
        $donHang->load('sanPham');

        return response()->json([
            'status' => true,
            'data' => $donHang,
        ]);
    }

    /**
     * Kiểm tra trạng thái thanh toán của đơn hàng
     * Dùng để frontend polling kiểm tra thanh toán thành công hay chưa
     */
    public function checkPaymentStatus($orderId)
    {
        $order = DonHang::find($orderId);
        
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đơn hàng',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $order->id,
                'ma_don_hang' => $order->ma_don_hang,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'is_paid' => $order->payment_status === 'paid',
                'order_status' => $order->status,
                'tong_tien' => $order->tong_tien
            ]
        ]);
    }

    /**
     * Lấy danh sách đơn hàng của buyer (người mua)
     */
    public function getBuyerOrders(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Pagination
        $perPage = min((int) $request->get('per_page', 20), 100);

        // Lấy đơn hàng theo khach_hang_id hoặc email/số điện thoại của user
        $query = DonHang::where(function($query) use ($user) {
                $query->where('khach_hang_id', $user->id)
                      ->orWhere(function($q) use ($user) {
                          // Nếu đơn hàng chưa có khach_hang_id nhưng có email/phone trùng
                          if ($user->email) {
                              $q->whereNull('khach_hang_id')
                                ->where('buyer_email', $user->email);
                          }
                          if ($user->so_dien_thoai) {
                              $q->orWhere(function($q2) use ($user) {
                                  $q2->whereNull('khach_hang_id')
                                     ->where('buyer_phone', $user->so_dien_thoai);
                              });
                          }
                      });
            })
            ->with(['sanPham:id,ten_san_pham,gia,hinh_anh,tinh_thanh,dia_chi,khach_hang_id', 'sanPham.khachHang:id,ho_va_ten,email,so_dien_thoai'])
            ->orderBy('created_at', 'desc');

        $paginated = $query->paginate($perPage);

        $orders = $paginated->getCollection()->map(function($order) {
                $productImages = $order->sanPham ? $order->sanPham->getImagesArray() : [];

                // Tính thời gian dự kiến giao hàng (3-5 ngày từ ngày đặt hàng)
                $estimatedDeliveryDate = \Carbon\Carbon::parse($order->created_at)->addDays(3)->format('Y-m-d H:i:s');
                $estimatedDeliveryDateFormatted = \Carbon\Carbon::parse($order->created_at)->addDays(3)->format('d/m/Y');

                // Lấy địa chỉ của seller (từ sản phẩm)
                $sellerLocation = $order->sanPham->tinh_thanh ?? $order->sanPham->dia_chi ?? 'Chưa cập nhật';

                return [
                    'id' => $order->id,
                    'order_code' => $order->ma_don_hang,
                    'product_id' => $order->san_pham_id,
                    'product_name' => $order->sanPham ? $order->sanPham->ten_san_pham : 'Sản phẩm đã bị xóa',
                    'product_image' => !empty($productImages) ? $productImages[0] : null,
                    'product_price' => $order->sanPham ? (float) $order->sanPham->gia : 0,
                    'quantity' => $order->so_luong,
                    'total_amount' => (float) $order->tong_tien,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'shipping_address' => $order->shipping_address,
                    'seller_location' => $sellerLocation,
                    'seller_name' => $order->sanPham && $order->sanPham->khachHang ? $order->sanPham->khachHang->ho_va_ten : 'Người bán',
                    'seller_phone' => $order->sanPham && $order->sanPham->khachHang ? $order->sanPham->khachHang->so_dien_thoai : null,
                    'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                    'order_date_formatted' => $order->created_at->format('d/m/Y H:i'),
                    'estimated_delivery_date' => $estimatedDeliveryDate,
                    'estimated_delivery_date_formatted' => $estimatedDeliveryDateFormatted,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Lấy danh sách đơn hàng của seller (người bán)
     */
    public function getSellerOrders(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Pagination
        $perPage = min((int) $request->get('per_page', 20), 100);

        // Lấy tất cả sản phẩm của seller
        $productIds = \App\Models\SanPham::where('khach_hang_id', $user->id)->pluck('id');

        $query = DonHang::whereIn('san_pham_id', $productIds)
            ->with(['sanPham:id,ten_san_pham,gia,hinh_anh', 'khachHang:id,ho_va_ten,email'])
            ->orderBy('created_at', 'desc');

        $paginated = $query->paginate($perPage);

        $orders = $paginated->getCollection()->map(function($order) {
                $productImages = $order->sanPham ? $order->sanPham->getImagesArray() : [];

                return [
                    'id' => $order->id,
                    'order_code' => $order->ma_don_hang,
                    'product_id' => $order->san_pham_id,
                    'product_name' => $order->sanPham ? $order->sanPham->ten_san_pham : 'Sản phẩm đã bị xóa',
                    'product_image' => !empty($productImages) ? $productImages[0] : null,
                    'buyer_name' => $order->buyer_name,
                    'buyer_phone' => $order->buyer_phone,
                    'buyer_email' => $order->buyer_email,
                    'buyer_address' => $order->shipping_address,
                    'quantity' => $order->so_luong,
                    'total_amount' => (float) $order->tong_tien,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $orders,
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng (seller xác nhận đã giao hàng)
     */
    public function updateOrderStatus(Request $request, DonHang $donHang)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền: seller chỉ có thể cập nhật đơn hàng của sản phẩm mình bán
        $product = $donHang->sanPham;
        if (!$product || $product->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền cập nhật đơn hàng này',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
        ]);

        $oldStatus = $donHang->status;
        $donHang->status = $request->status;
        $donHang->save();

        // Tạo thông báo cho buyer khi status thay đổi
        if ($oldStatus !== $request->status && $donHang->khach_hang_id) {
            $statusMap = [
                'confirmed' => 'confirmed',
                'shipped' => 'shipped',
                'delivered' => 'delivered',
                'completed' => 'completed',
            ];

            if (isset($statusMap[$request->status])) {
                \App\Http\Controllers\NotificationController::notifyOrder(
                    $donHang->khach_hang_id,
                    $donHang->ma_don_hang,
                    $statusMap[$request->status],
                    "/don-mua"
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'data' => $donHang,
        ]);
    }

    /**
     * Xác nhận đã nhận hàng (buyer)
     */
    public function confirmReceived(Request $request, DonHang $donHang)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền: chỉ buyer của đơn hàng mới có thể xác nhận
        if ($donHang->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền xác nhận đơn hàng này',
            ], 403);
        }

        // Chỉ có thể xác nhận khi đơn hàng đã được giao
        if (!in_array($donHang->status, ['shipped', 'delivered'])) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng chưa được giao, không thể xác nhận nhận hàng',
            ], 400);
        }

        $donHang->status = 'completed';
        $donHang->save();

        // Tạo thông báo cho seller khi buyer xác nhận nhận hàng
        if ($product && $product->khach_hang_id) {
            \App\Http\Controllers\NotificationController::create(
                $product->khach_hang_id,
                'order',
                'Khách hàng đã xác nhận nhận hàng',
                "Khách hàng đã xác nhận nhận hàng cho đơn hàng #{$donHang->ma_don_hang}",
                '✅',
                "/nguoi-ban/quan-ly-don-hang"
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã xác nhận nhận hàng thành công',
            'data' => $donHang,
        ]);
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus(Request $request, DonHang $donHang)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền: seller chỉ có thể cập nhật đơn hàng của sản phẩm mình bán
        $product = $donHang->sanPham;
        if (!$product || $product->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền cập nhật đơn hàng này',
            ], 403);
        }

        $request->validate([
            'payment_status' => 'required|in:pending,awaiting_payment,paid,completed,failed',
        ]);

        $donHang->payment_status = $request->payment_status;
        $donHang->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái thanh toán thành công',
            'data' => $donHang,
        ]);
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'DH' . strtoupper(Str::random(8));
        } while (DonHang::where('ma_don_hang', $code)->exists());

        return $code;
    }
    // ADMIN METHODS

    public function adminIndex(Request $request)
    {
        $query = DonHang::with(['sanPham', 'khachHang']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('ma_don_hang', 'like', "%$q%")
                  ->orWhere('buyer_name', 'like', "%$q%")
                  ->orWhere('buyer_phone', 'like', "%$q%");
        }

        $orders = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    public function updateStatus(Request $request, DonHang $donHang)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
            'payment_status' => 'nullable|in:pending,awaiting_payment,paid,completed,failed',
        ]);

        $donHang->status = $request->status;
        if ($request->filled('payment_status')) {
            $donHang->payment_status = $request->payment_status;
        }
        $donHang->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật đơn hàng thành công',
            'data' => $donHang
        ]);
    }

    public function laythongtinnganhang(Request $request){
        $request->validate([
            'order_id' => 'required|exists:don_hangs,id'
        ]);
        
        $donHang = DonHang::findOrFail($request->order_id);
        
        // Debug: Log order and product info
        \Log::info('Fetching bank info for order', [
            'order_id' => $donHang->id,
            'san_pham_id' => $donHang->san_pham_id
        ]);

        $noidung = SanPham::where('san_phams.id',$donHang->san_pham_id)
        ->join('khach_hangs','san_phams.khach_hang_id','=','khach_hangs.id')
        ->select('khach_hangs.ten_ngan_hang','khach_hangs.so_tai_khoan','khach_hangs.chu_tai_khoan')
        ->first();
        
        // Debug: Log the result
        \Log::info('Bank info result', [
            'result' => $noidung
        ]);
        
        // Check if bank info is empty
        if (!$noidung || !$noidung->ten_ngan_hang || !$noidung->so_tai_khoan) {
            return response()->json([
                'status' => false,
                'message' => 'Người bán chưa cập nhật đầy đủ thông tin ngân hàng (ten_ngan_hang, so_tai_khoan)',
                'data' => null
            ], 400);
        }
        
        return response()->json([
            'status' => true,
            'data' => $noidung
        ]);
    }

    /**
     * Tự động kiểm tra và cập nhật trạng thái thanh toán từ lịch sử giao dịch MBBank
     * API này được gọi định kỳ (cronjob) hoặc thủ công để kiểm tra các giao dịch mới
     */
    public function autoCheckPayment(Request $request)
    {
        try {
            // Lấy thông tin đăng nhập từ env hoặc config
            $username = env('MBBANK_USERNAME', '0775999005');
            $password = env('MBBANK_PASSWORD', 'Huyfender2782005@');
            $accountNumber = env('MBBANK_ACCOUNT_NUMBER', '0775999005');

            // Lấy ngày bắt đầu và kết thúc từ request hoặc mặc định là hôm nay
            $dayBegin = $request->input('day_begin', now()->format('d/m/Y'));
            $dayEnd = $request->input('day_end', now()->format('d/m/Y'));

            $payload = [
                "USERNAME"  => $username,
                "PASSWORD"  => $password,
                "DAY_BEGIN" => $dayBegin,
                "DAY_END"   => $dayEnd,
                "NUMBER_MB" => $accountNumber
            ];

            // Gọi API MBBank để lấy lịch sử giao dịch
            $client = new \GuzzleHttp\Client();
            $apiUrl = env('MBBANK_API_URL', 'https://api-mb.midstack.io.vn/api/transactions');
            
            $res = $client->request('POST', $apiUrl, [
                'json' => $payload,
                'timeout' => 30,
                'connect_timeout' => 10
            ]);

            $data = json_decode($res->getBody(), true);
            
            if (!isset($data['data']['transactionHistoryList'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không lấy được lịch sử giao dịch từ MBBank',
                    'data' => null
                ], 400);
            }

            $list_history = $data['data']['transactionHistoryList'];
            $updated_orders = [];
            $skipped_orders = [];

            foreach ($list_history as $transaction) {
                // Tìm mã đơn hàng từ description
                // 1. Tìm theo ID số: CHODC{id} hoặc DH{id}
                // 2. Tìm theo Mã đơn hàng: DH[A-Z0-9]+ (ví dụ DHO3Y9T85S)
                
                $id_don_hang = null;
                $ma_don_hang = null;
                $description = $transaction["description"];
                
                // Case 1: Tìm theo ID số
                if (preg_match('/CHODC(\d+)/i', $description, $matches)) {
                    $id_don_hang = (int)$matches[1];
                } elseif (preg_match('/DH(\d+)/i', $description, $matches)) {
                    $id_don_hang = (int)$matches[1];
                }
                
                // Case 2: Tìm theo Mã đơn hàng (nếu không tìm thấy ID số hoặc để chắc chắn)
                // Regex này tìm chuỗi bắt đầu bằng DH, theo sau là các ký tự chữ/số, độ dài từ 5-20 ký tự
                if (preg_match('/(DH[A-Z0-9]{5,20})/i', $description, $matchesCode)) {
                    $ma_don_hang = $matchesCode[1];
                }

                if (!$id_don_hang && !$ma_don_hang) {
                    continue; // Bỏ qua nếu không tìm thấy thông tin nào
                }

                // Query tìm đơn hàng
                $query = DonHang::query();
                
                if ($id_don_hang) {
                    $query->where('id', $id_don_hang);
                } elseif ($ma_don_hang) {
                    $query->where('ma_don_hang', $ma_don_hang);
                }
                
                $order = $query->where('payment_status', '!=', 'paid') // Chưa thanh toán
                    ->where('tong_tien', '<=', $transaction["creditAmount"]) // Khớp số tiền
                    ->whereIn('payment_method', ['vnpay', 'mbbank', 'momo']) // Chỉ thanh toán online
                    ->first();

                if ($order) {
                    // 1. Cập nhật trạng thái thanh toán
                    $order->payment_status = 'paid';
                    
                    // 2. Cập nhật trạng thái đơn hàng sang "Đã xác nhận" (confirmed) nếu đang là pending
                    if ($order->status === 'pending') {
                        $order->status = 'confirmed';
                    }

                    // 3. Lưu log giao dịch
                    $order->payment_payload = json_encode([
                        'transaction_id' => $transaction['refNo'] ?? null,
                        'transaction_date' => $transaction['transactionDate'] ?? null,
                        'amount' => $transaction['creditAmount'],
                        'description' => $transaction['description'],
                        'auto_verified_at' => now()->toDateTimeString(),
                        'note' => 'Auto-verified via MBBank API (Matched by ' . ($id_don_hang ? 'ID' : 'Code') . ')'
                    ]);
                    
                    $order->save();

                    $updated_orders[] = [
                        'order_id' => $order->id,
                        'ma_don_hang' => $order->ma_don_hang,
                        'amount' => $transaction['creditAmount'],
                        'transaction_id' => $transaction['refNo'] ?? null,
                        'new_status' => $order->status
                    ];

                    \Log::info("Auto-payment success for Order #{$order->id} ({$order->ma_don_hang}): Paid {$transaction['creditAmount']}");

                } else {
                    $skipped_orders[] = [
                        'suspected_id' => $id_don_hang,
                        'suspected_code' => $ma_don_hang,
                        'amount' => $transaction['creditAmount'],
                        'description' => $transaction['description'],
                        'reason' => 'Không tìm thấy đơn hàng khớp, sai số tiền, hoặc đã thanh toán'
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Kiểm tra thanh toán tự động hoàn tất',
                'data' => [
                    'total_transactions' => count($list_history),
                    'updated_orders' => $updated_orders,
                    'updated_count' => count($updated_orders),
                    'skipped_orders' => $skipped_orders,
                    'skipped_count' => count($skipped_orders),
                    'date_range' => [
                        'from' => $dayBegin,
                        'to' => $dayEnd
                    ]
                ]
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('MBBank API Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lỗi kết nối API MBBank: ' . $e->getMessage(),
                'data' => null
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Auto Check Payment Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}




