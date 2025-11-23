<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaiVietSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bai_viets')->truncate();

        $baiViets = [
            [
                'tieu_de' => 'Hướng dẫn mua hàng trên nền tảng',
                'noi_dung' => 'Bài viết hướng dẫn chi tiết cách mua hàng trên nền tảng của chúng tôi...',
                'hinh_anh' => null,
            ],
            [
                'tieu_de' => 'Cách đăng bán sản phẩm hiệu quả',
                'noi_dung' => 'Những mẹo và thủ thuật để đăng bán sản phẩm của bạn một cách hiệu quả nhất...',
                'hinh_anh' => null,
            ],
        ];

        foreach ($baiViets as $baiViet) {
            DB::table('bai_viets')->insert([
                'tieu_de' => $baiViet['tieu_de'],
                'noi_dung' => $baiViet['noi_dung'],
                'hinh_anh' => $baiViet['hinh_anh'],
                'khach_hang_id' => null,
                'is_active' => true,
                'luot_xem' => rand(50, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
