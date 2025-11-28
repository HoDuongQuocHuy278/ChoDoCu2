<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class KhachHang extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'khach_hangs';

    protected $fillable = [
        'ho_va_ten',
        'email',
        'so_dien_thoai',
        'password',
        'cccd',
        'ngay_sinh',
        'hash_reset',
        'hash_active',
        'is_active',
        'is_block',
        'is_seller',
        'ten_ngan_hang',
        'so_tai_khoan',
        'chu_tai_khoan',
        'dia_chi_ho_ten',
        'dia_chi_so_dien_thoai',
        'dia_chi_chi_tiet',
        'gioi_tinh',
        'last_password_change_at',
    ];

    protected $casts = [
        'last_password_change_at' => 'datetime',
    ];
}
