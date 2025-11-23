<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KhachHangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tắt foreign key checks tạm thời để có thể truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('khach_hangs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $khachHangs = [
            [
                'ho_va_ten' => 'Nguyễn Văn A',
                'email' => 'nguyenvana@example.com',
                'so_dien_thoai' => '0123456789',
                'password' => Hash::make('123456'),
                'cccd' => '001234567890',
                'ngay_sinh' => '1990-01-15',
                'hash_reset' => null,
                'hash_active' => null,
                'is_active' => 1,
                'is_block' => 0,
            ],
            [
                'ho_va_ten' => 'Trần Thị B',
                'email' => 'tranthib@example.com',
                'so_dien_thoai' => '0987654321',
                'password' => Hash::make('123456'),
                'cccd' => '001234567891',
                'ngay_sinh' => '1992-05-20',
                'hash_reset' => null,
                'hash_active' => null,
                'is_active' => 1,
                'is_block' => 0,
            ],
            [
                'ho_va_ten' => 'Lê Văn C',
                'email' => 'levanc@example.com',
                'so_dien_thoai' => '0912345678',
                'password' => Hash::make('123456'),
                'cccd' => '001234567892',
                'ngay_sinh' => '1988-11-10',
                'hash_reset' => null,
                'hash_active' => null,
                'is_active' => 1,
                'is_block' => 0,
            ],
            [
                'ho_va_ten' => 'Phạm Thị D',
                'email' => 'phamthid@example.com',
                'so_dien_thoai' => '0923456789',
                'password' => Hash::make('123456'),
                'cccd' => '001234567893',
                'ngay_sinh' => '1995-03-25',
                'hash_reset' => null,
                'hash_active' => null,
                'is_active' => 1,
                'is_block' => 0,
            ],
            [
                'ho_va_ten' => 'Hoàng Văn E',
                'email' => 'hoangvane@example.com',
                'so_dien_thoai' => '0934567890',
                'password' => Hash::make('123456'),
                'cccd' => '001234567894',
                'ngay_sinh' => '1993-07-18',
                'hash_reset' => null,
                'hash_active' => null,
                'is_active' => 1,
                'is_block' => 0,
            ],
        ];

        foreach ($khachHangs as $khachHang) {
            DB::table('khach_hangs')->insert([
                'ho_va_ten' => $khachHang['ho_va_ten'],
                'email' => $khachHang['email'],
                'so_dien_thoai' => $khachHang['so_dien_thoai'],
                'password' => $khachHang['password'],
                'cccd' => $khachHang['cccd'],
                'ngay_sinh' => $khachHang['ngay_sinh'],
                'hash_reset' => $khachHang['hash_reset'],
                'hash_active' => $khachHang['hash_active'],
                'is_active' => $khachHang['is_active'],
                'is_block' => $khachHang['is_block'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
