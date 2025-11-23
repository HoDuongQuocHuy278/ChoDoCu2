<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhMuc extends Model
{
    protected $table = 'danh_mucs';

    protected $fillable = [
        'ten_danh_muc',
        'slug',
        'mo_ta',
        'hinh_anh',
        'thu_tu',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'thu_tu' => 'integer',
    ];

    /**
     * Get the products for this category.
     */
    public function sanPhams(): HasMany
    {
        return $this->hasMany(SanPham::class, 'danh_muc_id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}




