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
}




