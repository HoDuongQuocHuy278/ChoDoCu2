<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\KhachHangSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chạy các seeder theo thứ tự
        $this->call([
            DanhMucSeeder::class,
            KhachHangSeeder::class,
            SanPhamSeeder::class,
            BaiVietSeeder::class,
        ]);
    }
}
