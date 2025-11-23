<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanhGia extends Model
{
    protected $table = 'danh_gias';

    protected $fillable = [
        'san_pham_id',
        'khach_hang_id',
        'diem',
        'noi_dung',
        'is_active',
    ];

    protected $casts = [
        'diem' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that was rated.
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Get the customer that made the rating.
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /**
     * Scope for active ratings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
