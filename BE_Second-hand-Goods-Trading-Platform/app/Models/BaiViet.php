<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaiViet extends Model
{
    protected $table = 'bai_viets';

    protected $fillable = [
        'tieu_de',
        'noi_dung',
        'hinh_anh',
        'khach_hang_id',
        'is_active',
        'luot_xem',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'luot_xem' => 'integer',
    ];

    /**
     * Get the customer that owns the post.
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /**
     * Scope for active posts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
