<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get list of chats for authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        
        // Get chats where user is either user1 or user2
        $chats = Chat::where(function($query) use ($user) {
                $query->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
            })
            ->with(['user1:id,ho_va_ten,email', 'user2:id,ho_va_ten,email', 'sanPham:id,ten_san_pham,hinh_anh'])
            ->withCount(['messages as unread_count' => function($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                      ->where('is_read', false);
            }])
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->paginate($perPage);

        $formattedChats = $chats->getCollection()->map(function($chat) use ($user) {
            $otherUser = $chat->getOtherUser($user->id);
            $lastMessage = $chat->messages->first();

            return [
                'id' => $chat->id,
                'name' => $otherUser ? $otherUser->ho_va_ten : 'Người dùng',
                'participant_name' => $otherUser ? $otherUser->ho_va_ten : 'Người dùng',
                'participant_id' => $otherUser ? $otherUser->id : null,
                'avatar' => null, // TODO: Add avatar to khach_hangs table
                'is_online' => false, // TODO: Implement online status
                'last_message' => $lastMessage ? $lastMessage->content : null,
                'last_message_time' => $lastMessage ? $lastMessage->created_at->toISOString() : $chat->created_at->toISOString(),
                'last_message_type' => $lastMessage ? $lastMessage->type : 'text',
                'unread_count' => $chat->unread_count ?? 0,
                'product_title' => $chat->sanPham ? $chat->sanPham->ten_san_pham : null,
                'product_id' => $chat->san_pham_id,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $formattedChats,
                'current_page' => $chats->currentPage(),
                'last_page' => $chats->lastPage(),
                'per_page' => $chats->perPage(),
                'total' => $chats->total(),
            ],
        ]);
    }

    /**
     * Get or create chat with another user
     */
    public function createOrGet(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $validator = Validator::make($request->all(), [
            'seller_id' => 'required|integer|exists:khach_hangs,id',
            'product_id' => 'nullable|integer|exists:san_phams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sellerId = (int) $request->seller_id;
        $productId = $request->has('product_id') ? (int) $request->product_id : null;

        if ($user->id === $sellerId) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không thể chat với chính mình',
            ], 400);
        }

        // Create or get chat
        $chat = Chat::getOrCreate($user->id, $sellerId, $productId);

        return response()->json([
            'status' => true,
            'message' => 'Chat đã được tạo hoặc lấy thành công',
            'data' => [
                'chat_id' => $chat->id,
                'room' => $chat->room,
            ],
        ]);
    }

    /**
     * Get messages for a chat
     */
    public function getMessages(Request $request, $chat)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Handle both ID and room identifier
        if (!($chat instanceof Chat)) {
            if (is_numeric($chat)) {
                $chat = Chat::find($chat);
            } else {
                // Try to find by room
                $chat = Chat::where('room', $chat)->first();
            }
            
            if (!$chat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy chat',
                ], 404);
            }
        }

        // Check if user is part of this chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền xem chat này',
            ], 403);
        }

        $perPage = min((int) $request->get('per_page', 50), 100);
        
        $messages = ChatMessage::where('chat_id', $chat->id)
            ->with('sender:id,ho_va_ten,email')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        $formattedMessages = $messages->getCollection()->map(function($message) use ($user) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'content' => $message->content,
                'type' => $message->type,
                'file_name' => $message->file_name,
                'file_size' => $message->file_size,
                'created_at' => $message->created_at->toISOString(),
                'status' => 'delivered', // TODO: Implement message status
                'is_read' => $message->is_read,
            ];
        });

        // Mark messages as read
        ChatMessage::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Update last_message_at
        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $formattedMessages,
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, $chat)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Handle both ID and room identifier
        if (!($chat instanceof Chat)) {
            if (is_numeric($chat)) {
                $chat = Chat::find($chat);
            } else {
                // Try to find by room
                $chat = Chat::where('room', $chat)->first();
            }
            
            if (!$chat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy chat',
                ], 404);
            }
        }

        // Check if user is part of this chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền gửi tin nhắn trong chat này',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:5000',
            'type' => 'nullable|string|in:text,image,file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 400);
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'file_name' => $request->file_name,
            'file_size' => $request->file_size,
        ]);

        // Update last_message_at
        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Tin nhắn đã được gửi',
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'content' => $message->content,
                'type' => $message->type,
                'created_at' => $message->created_at->toISOString(),
                'status' => 'delivered',
                'is_read' => false,
            ],
        ], 201);
    }

    /**
     * Get chat info
     */
    public function show($chat)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Handle both ID and room identifier
        if (!($chat instanceof Chat)) {
            if (is_numeric($chat)) {
                $chat = Chat::find($chat);
            } else {
                // Try to find by room
                $chat = Chat::where('room', $chat)->first();
            }
            
            if (!$chat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy chat',
                ], 404);
            }
        }

        // Check if user is part of this chat
        if ($chat->user1_id !== $user->id && $chat->user2_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền xem chat này',
            ], 403);
        }

        $otherUser = $chat->getOtherUser($user->id);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $chat->id,
                'room' => $chat->room,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->ho_va_ten,
                    'email' => $otherUser->email,
                ],
                'product' => $chat->sanPham ? [
                    'id' => $chat->sanPham->id,
                    'title' => $chat->sanPham->ten_san_pham,
                    'price' => $chat->sanPham->gia,
                    'image' => $chat->sanPham->getFirstImage(),
                ] : null,
            ],
        ]);
    }

    /**
     * Get unread messages count
     */
    public function unreadCount()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $chats = Chat::where(function($query) use ($user) {
                $query->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
            })
            ->pluck('id');

        $unreadCount = ChatMessage::whereIn('chat_id', $chats)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'count' => $unreadCount,
            ],
        ]);
    }
}

