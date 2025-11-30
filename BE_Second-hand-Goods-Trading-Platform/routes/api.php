<?php

use App\Http\Controllers\BaiVietController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DanhMucController;
use App\Http\Controllers\DangKyController;
use App\Http\Controllers\DangNhapController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuenMatKhauController;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\ThanhToanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum ');

    // KHACH HANG
    Route::get('/khach-hang/get-data', [KhachHangController::class, 'getData']);
    Route::post('/khach-hang/add-data', [KhachHangController::class, 'addData']);
    Route::post('/khach-hang/update', [KhachHangController::class, 'update']);
    Route::post('/khach-hang/delete', [KhachHangController::class, 'destroy']);
    Route::post('/khach-hang/change-status', [KhachHangController::class, 'changeStatus']);
    Route::post('/khach-hang/change-active', [KhachHangController::class, 'changeActive']);
    Route::post('/khach-hang/tim-kiem', [KhachHangController::class, 'search']);
    Route::get('/khach-hang/xuat-excel', [KhachHangController::class, 'xuatExcel']);


    Route::post('/client/dang-nhap', [KhachHangController::class, 'dangNhap']);
    Route::post('/client/dang-ky', [KhachHangController::class, 'dangKy']);
    Route::get('/client/dang-xuat', [KhachHangController::class, 'dangXuat']);
    Route::post('/client/quen-mat-khau', [KhachHangController::class, 'quenMatKhau']);
    Route::post('/client/dat-lai-mat-khau', [KhachHangController::class, 'datLaiMatKhau']);
    Route::post('/client/kich-hoat', [KhachHangController::class, 'kichHoat']);
    Route::get('/client/check-token', [KhachHangController::class, 'checkClient']);
    Route::get('/client/thong-tin', [KhachHangController::class, 'thongTinNguoiDung']);
    Route::post('/client/doi-mat-khau', [KhachHangController::class, 'doiMatKhau']);
    Route::get('/client/check-seller-status', [KhachHangController::class, 'checkSellerStatus'])->middleware('auth:sanctum');
    Route::post('/client/dang-ky-ban', [KhachHangController::class, 'dangKyBan'])->middleware('auth:sanctum');

    // Profile routes
    Route::prefix('client')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [KhachHangController::class, 'getProfile']);
        Route::put('/profile', [KhachHangController::class, 'updateProfile']);
        Route::put('/profile/bank', [KhachHangController::class, 'updateBank']);
        Route::put('/profile/address', [KhachHangController::class, 'updateAddress']);
        Route::put('/profile/password', [KhachHangController::class, 'updatePassword']);
    });


    // Danh mục
    Route::prefix('client')->group(function () {
        Route::get('/danh-muc', [DanhMucController::class, 'index']);
        Route::get('/danh-muc/{slug}', [DanhMucController::class, 'show']);
        Route::get('/danh-muc/{slug}/san-pham', [DanhMucController::class, 'sanPham']);
    });

    // Sản Phẩm
    Route::prefix('client')->group(function () {
        Route::get('/san-pham', [SanPhamController::class, 'index']);
        Route::get('/san-pham/{san_pham}', [SanPhamController::class, 'show']);
        Route::get('/san-pham/{san_pham}/similar', [SanPhamController::class, 'similar']);
        // Đăng bán sản phẩm (yêu cầu đăng nhập)
        Route::middleware(['auth:sanctum'])->post('/san-pham', [SanPhamController::class, 'storeClient']);
        // Quản lý sản phẩm của seller
        Route::middleware(['auth:sanctum'])->get('/seller/product-stats', [SanPhamController::class, 'getSellerProductStats']);
        Route::middleware(['auth:sanctum'])->get('/seller/sales-history', [SanPhamController::class, 'getSellerSalesHistory']);
        Route::middleware(['auth:sanctum'])->get('/seller/san-pham/{san_pham}/orders', [SanPhamController::class, 'getProductOrders']);
        Route::middleware(['auth:sanctum'])->get('/seller/san-pham/{san_pham}/reviews', [SanPhamController::class, 'getProductReviews']);
        Route::middleware(['auth:sanctum'])->put('/seller/san-pham/{san_pham}', [SanPhamController::class, 'updateSellerProduct']);
        Route::middleware(['auth:sanctum'])->delete('/seller/san-pham/{san_pham}', [SanPhamController::class, 'deleteSellerProduct']);
    });

    // Đơn hàng (Buy now)
    Route::prefix('client')->group(function () {
        Route::post('/don-hang', [DonHangController::class, 'store']);
        Route::get('/don-hang/{don_hang}', [DonHangController::class, 'show']);
        // Đơn hàng của buyer
        Route::middleware(['auth:sanctum'])->get('/don-hang-mua', [DonHangController::class, 'getBuyerOrders']);
        Route::middleware(['auth:sanctum'])->post('/don-hang/{don_hang}/xac-nhan-nhan-hang', [DonHangController::class, 'confirmReceived']);
        // Đơn hàng của seller
        Route::middleware(['auth:sanctum'])->get('/don-hang-ban', [DonHangController::class, 'getSellerOrders']);
        Route::middleware(['auth:sanctum'])->put('/don-hang/{don_hang}/trang-thai', [DonHangController::class, 'updateOrderStatus']);
        Route::middleware(['auth:sanctum'])->put('/don-hang/{don_hang}/thanh-toan', [DonHangController::class, 'updatePaymentStatus']);
    });

    // Thông báo
    Route::prefix('client')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/thong-bao', [NotificationController::class, 'index']);
        Route::get('/thong-bao/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/thong-bao/{notification}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/thong-bao/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    // Chat
    Route::prefix('client')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/chat', [ChatController::class, 'index']);
        Route::post('/chat/create-or-get', [ChatController::class, 'createOrGet']);
        Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
        Route::get('/chat/{chat}', [ChatController::class, 'show']);
        Route::get('/chat/{chat}/messages', [ChatController::class, 'getMessages']);
        Route::post('/chat/{chat}/messages', [ChatController::class, 'sendMessage']);
    });

    // Route cho frontend đang dùng
    Route::middleware(['auth:sanctum'])->post('/listings', [SanPhamController::class, 'storeClient']);

    // Bài viết
    Route::prefix('bai-viet')->group(function () {
        Route::get('/', [BaiVietController::class, 'index']);
        Route::get('/{bai_viet}', [BaiVietController::class, 'show']);
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/', [BaiVietController::class, 'store']);
            Route::put('/{bai_viet}', [BaiVietController::class, 'update']);
            Route::delete('/{bai_viet}', [BaiVietController::class, 'destroy']);
            Route::post('/{bai_viet}/toggle-active', [BaiVietController::class, 'toggleActive']);
        });
    });

    // ADMIN (quản trị)
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        // Dashboard Stats
        Route::get('/stats', [KhachHangController::class, 'adminStats']);

        // Quản lý người dùng
        Route::get('/users', [KhachHangController::class, 'adminIndex']);
        Route::post('/users/{id}/role', [KhachHangController::class, 'updateRole']);
        Route::post('/users/{id}/block', [KhachHangController::class, 'toggleBlock']);
        Route::delete('/users/{id}', [KhachHangController::class, 'adminDestroy']);

        // Quản lý danh mục
        Route::get('/categories', [DanhMucController::class, 'index']); // Reuse existing
        Route::post('/categories', [DanhMucController::class, 'store']);
        Route::put('/categories/{danh_muc}', [DanhMucController::class, 'update']);
        Route::delete('/categories/{danh_muc}', [DanhMucController::class, 'destroy']);

        // Quản lý sản phẩm
        Route::get('/products', [SanPhamController::class, 'adminIndex']);
        Route::post('/products/{san_pham}/toggle', [SanPhamController::class, 'toggleActive']);
        Route::delete('/products/{san_pham}', [SanPhamController::class, 'destroy']);

        // Quản lý đơn hàng
        Route::get('/orders', [DonHangController::class, 'adminIndex']);
        Route::put('/orders/{don_hang}/status', [DonHangController::class, 'updateStatus']);
    });
// Thanh toán
Route::prefix('client')->group(function () {
    // VNPay
    Route::post('/payment/vnpay', [ThanhToanController::class, 'vnpay_payment']);
    Route::get('/payment/vnpay/callback', [ThanhToanController::class, 'vnpayCallback']);
    Route::post('/payment/vnpay/refund', [ThanhToanController::class, 'vnpayRefund'])->middleware('auth:sanctum');
    Route::post('/payment/vnpay/query', [ThanhToanController::class, 'vnpayQuery'])->middleware('auth:sanctum');

    // MBBank
    Route::post('/payment/mbbank', [ThanhToanController::class, 'mbbank_payment']);
    Route::post('/payment/qr', [DonHangController::class, 'laythongtinnganhang']);
    // Test endpoint (có thể xóa sau khi debug xong)
    Route::get('/payment/mbbank/test', [ThanhToanController::class, 'testMbbankApi']);
});

// Route cũ để tương thích
Route::post('/vnpay_payment', [ThanhToanController::class, 'vnpay_payment']);
