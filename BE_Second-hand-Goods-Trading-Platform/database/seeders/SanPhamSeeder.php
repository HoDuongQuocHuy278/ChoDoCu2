<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\KhachHang;
use App\Models\DanhMuc;

class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh mục và khách hàng để tạo sản phẩm
        $danhMucs = DanhMuc::all();
        $khachHangs = KhachHang::all();

        if ($danhMucs->isEmpty() || $khachHangs->isEmpty()) {
            $this->command->warn('Vui lòng chạy DanhMucSeeder và KhachHangSeeder trước!');
            return;
        }

        // Tắt foreign key checks tạm thời để có thể truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('san_phams')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sanPhams = [

            // ======= ĐIỆN THOẠI =======
            [
                'ten_san_pham' => 'iPhone 13 128GB',
                'mo_ta' => 'iPhone 13 128GB màu xanh, còn bảo hành đến 12/2024',
                'gia' => 15000000,
                'hinh_anh' => json_encode([
                    'https://images.unsplash.com/photo-1516466723877-e4ec1d736c8a'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'dien-thoai',
                'thuong_hieu' => 'Apple',
                'mau_sac' => 'Xanh',
                'tinh_thanh' => 'Hà Nội',
                'trang_thai' => 1,
                'luot_xem' => 0,
            ],
            [
                'ten_san_pham' => 'iPhone 11 64GB',
                'mo_ta' => 'iPhone 11 màu đen, pin 89%, ngoại hình đẹp',
                'gia' => 7000000,
                'hinh_anh' => json_encode([
                    '/storage/products/a5UhTZnoDavpkfUCweVWyCxP6SR7ILCdrqqrcuuA.png'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'dien-thoai',
                'thuong_hieu' => 'Apple',
                'mau_sac' => 'Đen',
                'tinh_thanh' => 'Đà Nẵng',
                'trang_thai' => 1,
                'luot_xem' => 12,
            ],
            [
                'ten_san_pham' => 'Samsung Galaxy S21 Ultra',
                'mo_ta' => 'Samsung S21 Ultra đẹp 98%, full chức năng',
                'gia' => 9000000,
                'hinh_anh' => json_encode([
                    '/storage/products/ZJjxrLqgaZLF2O4WxGnPMvnq6i4zPQE2ASsBPOYs.jpg'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'dien-thoai',
                'thuong_hieu' => 'Samsung',
                'mau_sac' => 'Bạc',
                'tinh_thanh' => 'Hồ Chí Minh',
                'trang_thai' => 1,
                'luot_xem' => 20,
            ],

            // ======= LAPTOP =======
            [
                'ten_san_pham' => 'Laptop Dell Inspiron 15',
                'mo_ta' => 'Dell Inspiron, RAM 8GB, SSD 256GB, pin còn tốt',
                'gia' => 8000000,
                'hinh_anh' => json_encode([
                    '/storage/products/iW6SeM5i44cguO3ObwyWk5nMGPEvJ9qD0J16TTHR.png'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'may-tinh',
                'thuong_hieu' => 'Dell',
                'mau_sac' => 'Đen',
                'tinh_thanh' => 'Đà Nẵng',
                'trang_thai' => 1,
                'luot_xem' => 5,
            ],
            [
                'ten_san_pham' => 'MacBook Pro 2019 16 inch',
                'mo_ta' => 'MacBook Pro 16 inch, Core i7, RAM 16GB, SSD 512GB',
                'gia' => 24000000,
                'hinh_anh' => json_encode([
                    '/storage/products/VL0qqG7pQXXk83vyVrbHXn189dTr8GXzK5on7EWJ.png'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'may-tinh',
                'thuong_hieu' => 'Apple',
                'mau_sac' => 'Xám',
                'tinh_thanh' => 'Hà Nội',
                'trang_thai' => 1,
                'luot_xem' => 40,
            ],
            [
                'ten_san_pham' => 'Asus TUF Gaming F15',
                'mo_ta' => 'Laptop gaming, RTX 3050, màn 144Hz',
                'gia' => 16000000,
                'hinh_anh' => json_encode([
                    '/storage/products/PUVwwL3MhHWrYwvdYZdWg9vyxoN0XmYMbkGp2xFp.png'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'may-tinh',
                'thuong_hieu' => 'Asus',
                'mau_sac' => 'Đen',
                'tinh_thanh' => 'Cần Thơ',
                'trang_thai' => 1,
                'luot_xem' => 33,
            ],

            // ======= SÁCH =======
            [
                'ten_san_pham' => 'Sách "Đắc Nhân Tâm"',
                'mo_ta' => 'Sách Đắc Nhân Tâm bản gốc, còn mới',
                'gia' => 50000,
                'hinh_anh' => json_encode([
                    '/storage/products/vjpTreRpZtYfvVIl5q26G2uoRFYfDfWsMW64Uhy1.png'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'sach',
                'thuong_hieu' => 'NXB Trẻ',
                'mau_sac' => null,
                'tinh_thanh' => 'Hồ Chí Minh',
                'trang_thai' => 1,
                'luot_xem' => 2,
            ],
            [
                'ten_san_pham' => 'Sách "Nhà Giả Kim"',
                'mo_ta' => 'Bản bìa cứng, đẹp như mới',
                'gia' => 70000,
                'hinh_anh' => json_encode([
                    '/storage/products/X0YzVUEdPSxmwoF96rMnmjXLU9vKwpTh6g2wiEcf.png'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'sach',
                'thuong_hieu' => 'NXB Văn Học',
                'mau_sac' => 'Vàng',
                'tinh_thanh' => 'Hà Nội',
                'trang_thai' => 1,
                'luot_xem' => 5,
            ],
            [
                'ten_san_pham' => 'Sách giáo trình lập trình Java',
                'mo_ta' => 'Giáo trình Java đại học, tình trạng tốt',
                'gia' => 45000,
                'hinh_anh' => json_encode([
                    '/storage/products/vjpTreRpZtYfvVIl5q26G2uoRFYfDfWsMW64Uhy1.png'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'sach',
                'thuong_hieu' => null,
                'mau_sac' => 'Xanh',
                'tinh_thanh' => 'Đà Nẵng',
                'trang_thai' => 1,
                'luot_xem' => 11,
            ],

            // ======= ĐỒ GIA DỤNG =======
            [
                'ten_san_pham' => 'Quạt đứng Panasonic',
                'mo_ta' => 'Quạt đứng Panasonic 3 tốc độ, hoạt động tốt',
                'gia' => 300000,
                'hinh_anh' => json_encode([
                    '/storage/products/j3Bxh64WkuRRMo1skwiwOMpIrnUHDeTRQlHWA24V.jpg'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'gia-dung',
                'thuong_hieu' => 'Panasonic',
                'mau_sac' => 'Trắng',
                'tinh_thanh' => 'Hải Phòng',
                'trang_thai' => 1,
                'luot_xem' => 3,
            ],
            [
                'ten_san_pham' => 'Máy hút bụi Xiaomi',
                'mo_ta' => 'Máy hút bụi cầm tay Xiaomi Mijia',
                'gia' => 800000,
                'hinh_anh' => json_encode([
                    '/storage/products/6Kyah1wiWlCY5jupi8b2h46UFlKWjgy2QhRR84gR.jpg'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'gia-dung',
                'thuong_hieu' => 'Xiaomi',
                'mau_sac' => 'Trắng',
                'tinh_thanh' => 'Hồ Chí Minh',
                'trang_thai' => 1,
                'luot_xem' => 8,
            ],

            // ======= THỜI TRANG =======
            [
                'ten_san_pham' => 'Áo sơ mi nam Uniqlo',
                'mo_ta' => 'Áo sơ mi Uniqlo đẹp như mới, size M',
                'gia' => 150000,
                'hinh_anh' => json_encode([
                    '/storage/products/lEKyuyrrYgFqzvb2fKZZQJsIk9QnXvlipc63O4rS.jpg'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'thoi-trang',
                'thuong_hieu' => 'Uniqlo',
                'mau_sac' => 'Trắng',
                'tinh_thanh' => 'Huế',
                'trang_thai' => 1,
                'luot_xem' => 1,
            ],
            [
                'ten_san_pham' => 'Giày Nike Air Force 1',
                'mo_ta' => 'Nike Air Force 1 chính hãng, size 42, đẹp 95%',
                'gia' => 1200000,
                'hinh_anh' => json_encode([
                    '/storage/products/ZJjxrLqgaZLF2O4WxGnPMvnq6i4zPQE2ASsBPOYs.jpg'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'thoi-trang',
                'thuong_hieu' => 'Nike',
                'mau_sac' => 'Trắng',
                'tinh_thanh' => 'Đà Nẵng',
                'trang_thai' => 1,
                'luot_xem' => 14,
            ],

            // ======= PHỤ KIỆN =======
            [
                'ten_san_pham' => 'Tai nghe AirPods Pro',
                'mo_ta' => 'AirPods Pro bản 2022, chống ồn, full box',
                'gia' => 3500000,
                'hinh_anh' => json_encode([
                    '/storage/products/WZQnoiIh8EmdmyHEURUthwoislDwLFurtiq3hXuW.png'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'phu-kien',
                'thuong_hieu' => 'Apple',
                'mau_sac' => 'Trắng',
                'tinh_thanh' => 'Cần Thơ',
                'trang_thai' => 1,
                'luot_xem' => 7,
            ],

            // ======= ĐỒ CHƠI =======
            [
                'ten_san_pham' => 'Lego City 2023',
                'mo_ta' => 'Bộ Lego City 2023 nguyên seal',
                'gia' => 900000,
                'hinh_anh' => json_encode([
                    '/storage/products/X0YzVUEdPSxmwoF96rMnmjXLU9vKwpTh6g2wiEcf.png'
                ]),
                'tinh_trang' => 'moi',
                'category' => 'do-choi',
                'thuong_hieu' => 'Lego',
                'mau_sac' => 'Nhiều màu',
                'tinh_thanh' => 'Hồ Chí Minh',
                'trang_thai' => 1,
                'luot_xem' => 6,
            ],

            // ======= XE =======
            [
                'ten_san_pham' => 'Xe đạp thể thao Giant',
                'mo_ta' => 'Xe Giant chạy tốt, phanh đĩa, vành 700c',
                'gia' => 2500000,
                'hinh_anh' => json_encode([
                    '/storage/products/ox3FgHOglEFbRehE5KYWc4Ja9r2MucyOP0sJtxci.png'
                ]),
                'tinh_trang' => 'cu',
                'category' => 'xe',
                'thuong_hieu' => 'Giant',
                'mau_sac' => 'Đen',
                'tinh_thanh' => 'Hải Phòng',
                'trang_thai' => 1,
                'luot_xem' => 12,
            ],
        ];


        foreach ($sanPhams as $index => $sanPham) {
            // Tìm danh mục theo slug thay vì random
            $danhMuc = $danhMucs->firstWhere('slug', $sanPham['category']);
            // Nếu không tìm thấy, dùng random làm fallback
            if (!$danhMuc) {
                $danhMuc = $danhMucs->random();
            }
            $khachHang = $khachHangs->random();

            DB::table('san_phams')->insert([
                'ten_san_pham' => $sanPham['ten_san_pham'],
                'mo_ta' => $sanPham['mo_ta'],
                'gia' => $sanPham['gia'],
                'hinh_anh' => $sanPham['hinh_anh'],
                'tinh_trang' => $sanPham['tinh_trang'],
                'category' => $sanPham['category'],
                'danh_muc_id' => $danhMuc->id,
                'thuong_hieu' => $sanPham['thuong_hieu'],
                'mau_sac' => $sanPham['mau_sac'],
                'tinh_thanh' => $sanPham['tinh_thanh'],
                'khach_hang_id' => $khachHang->id,
                'trang_thai' => $sanPham['trang_thai'],
                'luot_xem' => $sanPham['luot_xem'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
