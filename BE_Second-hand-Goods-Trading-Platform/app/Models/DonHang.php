<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonHang extends Model
{
    protected $fillable = [
        'ma_don_hang',
        'san_pham_id',
        'khach_hang_id',
        'so_luong',
        'tong_tien',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'shipping_address',
        'notes',
        'payment_method',
        'payment_status',
        'status',
        'payment_payload',
    ];

    protected $casts = [
        'payment_payload' => 'array',
        'tong_tien' => 'decimal:2',
    ];

    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }
}
