<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $fillable = [
        'user1_id',
        'user2_id',
        'san_pham_id',
        'room',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get user1
     */
    public function user1(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'user1_id');
    }

    /**
     * Get user2
     */
    public function user2(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'user2_id');
    }

    /**
     * Get product (if chat is about a product)
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Get messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the other user in the chat
     */
    public function getOtherUser(int $currentUserId): ?KhachHang
    {
        if ($this->user1_id === $currentUserId) {
            return $this->user2;
        }
        if ($this->user2_id === $currentUserId) {
            return $this->user1;
        }
        return null;
    }

    /**
     * Generate room identifier
     */
    public static function generateRoom(int $userId1, int $userId2): string
    {
        $min = min($userId1, $userId2);
        $max = max($userId1, $userId2);
        return "chat:{$min}-{$max}";
    }

    /**
     * Get or create chat between two users
     */
    public static function getOrCreate(int $userId1, int $userId2, ?int $productId = null): self
    {
        // Ensure consistent ordering
        if ($userId1 > $userId2) {
            [$userId1, $userId2] = [$userId2, $userId1];
        }

        $room = self::generateRoom($userId1, $userId2);

        return self::firstOrCreate(
            [
                'user1_id' => $userId1,
                'user2_id' => $userId2,
                'san_pham_id' => $productId,
            ],
            [
                'room' => $room,
            ]
        );
    }
}

