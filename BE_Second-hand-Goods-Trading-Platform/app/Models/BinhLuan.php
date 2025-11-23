<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BinhLuan extends Model
{
    protected $table = 'binh_luans';

    protected $fillable = [
        'san_pham_id',
        'khach_hang_id',
        'noi_dung',
        'binh_luan_cha_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns the comment.
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Get the customer that made the comment.
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /**
     * Get the parent comment.
     */
    public function binhLuanCha(): BelongsTo
    {
        return $this->belongsTo(BinhLuan::class, 'binh_luan_cha_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(BinhLuan::class, 'binh_luan_cha_id');
    }

    /**
     * Scope for active comments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
