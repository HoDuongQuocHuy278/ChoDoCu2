<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của user
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = Notification::where('khach_hang_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        $paginated = $query->paginate($perPage);

        $notifications = $paginated->getCollection()->map(function($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'action_url' => $notification->action_url,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'data' => $notification->data,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $notifications,
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'unread_count' => Notification::where('khach_hang_id', $user->id)
                    ->where('is_read', false)
                    ->count(),
            ],
        ]);
    }

    /**
     * Đánh dấu một thông báo đã đọc
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền sở hữu
        if ($notification->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu đã đọc',
            'data' => $notification,
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $updated = Notification::where('khach_hang_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu tất cả đã đọc',
            'updated_count' => $updated,
        ]);
    }

    /**
     * Đếm số thông báo chưa đọc
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $count = Notification::where('khach_hang_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Tạo thông báo (helper method - có thể gọi từ các controller khác)
     */
    public static function create($khachHangId, $type, $title, $message, $icon = null, $actionUrl = null, $data = null)
    {
        return Notification::create([
            'khach_hang_id' => $khachHangId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }

    /**
     * Tạo thông báo đơn hàng
     */
    public static function notifyOrder($khachHangId, $orderCode, $status = 'created', $actionUrl = null)
    {
        $messages = [
            'created' => ['title' => 'Đơn hàng mới', 'message' => "Bạn có đơn hàng mới #{$orderCode}"],
            'paid' => ['title' => 'Thanh toán thành công', 'message' => "Đơn hàng #{$orderCode} đã được thanh toán thành công"],
            'confirmed' => ['title' => 'Đơn hàng đã xác nhận', 'message' => "Đơn hàng #{$orderCode} đã được xác nhận"],
            'shipped' => ['title' => 'Đơn hàng đã giao', 'message' => "Đơn hàng #{$orderCode} đã được giao hàng"],
            'delivered' => ['title' => 'Đơn hàng đã đến nơi', 'message' => "Đơn hàng #{$orderCode} đã đến nơi"],
            'completed' => ['title' => 'Đơn hàng hoàn thành', 'message' => "Đơn hàng #{$orderCode} đã hoàn thành"],
        ];

        $msg = $messages[$status] ?? $messages['created'];

        return self::create(
            $khachHangId,
            'order',
            $msg['title'],
            $msg['message'],
            '📦',
            $actionUrl ?? "/don-mua"
        );
    }

    /**
     * Tạo thông báo sản phẩm
     */
    public static function notifyProduct($khachHangId, $productName, $status = 'approved', $actionUrl = null)
    {
        $messages = [
            'approved' => ['title' => 'Sản phẩm đã được duyệt', 'message' => "Sản phẩm \"{$productName}\" đã được duyệt và hiển thị"],
            'rejected' => ['title' => 'Sản phẩm bị từ chối', 'message' => "Sản phẩm \"{$productName}\" đã bị từ chối"],
            'sold' => ['title' => 'Sản phẩm đã bán', 'message' => "Sản phẩm \"{$productName}\" đã được bán"],
        ];

        $msg = $messages[$status] ?? $messages['approved'];

        return self::create(
            $khachHangId,
            'product',
            $msg['title'],
            $msg['message'],
            '📦',
            $actionUrl ?? "/nguoi-ban/san-pham"
        );
    }
}
