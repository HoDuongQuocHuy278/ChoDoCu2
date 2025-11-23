<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DanhMucSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('danh_mucs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $danhMucs = [
            [
                'ten_danh_muc' => 'Điện thoại',
                'slug' => 'dien-thoai',
                'mo_ta' => 'Điện thoại di động, smartphone',
                'hinh_anh' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9',
                'thu_tu' => 1,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Laptop & Máy tính',
                'slug' => 'may-tinh',
                'mo_ta' => 'Laptop, PC, màn hình, linh kiện',
                'hinh_anh' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8',
                'thu_tu' => 2,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Sách',
                'slug' => 'sach',
                'mo_ta' => 'Sách văn học, giáo khoa, kỹ năng',
                'hinh_anh' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f',
                'thu_tu' => 3,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Thời trang Nam',
                'slug' => 'thoi-trang-nam',
                'mo_ta' => 'Quần áo, giày dép, phụ kiện nam',
                'hinh_anh' => 'https://file.hstatic.net/200000887901/file/ao-so-mi-nam-aristino-ass085s9x900x900x4.webp',
                'thu_tu' => 4,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Thời trang Nữ',
                'slug' => 'thoi-trang-nu',
                'mo_ta' => 'Đầm váy, áo quần nữ, phụ kiện thời trang',
                'hinh_anh' => 'https://cdn.kkfashion.vn/30691-home_default/dam-xoe-xep-ly-dang-dai-nhan-eo-hl27-31.jpg',
                'thu_tu' => 5,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Giày dép',
                'slug' => 'giay-dep',
                'mo_ta' => 'Giày nam, giày nữ, sneaker, sandal',
                'hinh_anh' => 'https://myshoes.vn/image/cache/catalog/2024/blog/linh/a-570280a2-05e3-431b-a6bf-82dd03-1140x600h.png',
                'thu_tu' => 6,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Phụ kiện & Đồng hồ',
                'slug' => 'phu-kien',
                'mo_ta' => 'Đồng hồ, ví, thắt lưng, túi xách',
                'hinh_anh' => 'https://images.unsplash.com/photo-1511376777868-611b54f68947',
                'thu_tu' => 7,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Gia dụng',
                'slug' => 'gia-dung',
                'mo_ta' => 'Đồ dùng nhà bếp, quạt, nồi cơm, máy ép',
                'hinh_anh' => 'https://file.hstatic.net/200000868155/file/do-gia-dung-la-gi-do-dien-gia-dung-gom-nhung-gi-18-10-2022-1.jpg',
                'thu_tu' => 8,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Đồ điện tử',
                'slug' => 'do-dien-tu',
                'mo_ta' => 'Thiết bị điện tử, loa, camera, TV',
                'hinh_anh' => 'https://images.unsplash.com/photo-1518779578993-ec3579fee39f',
                'thu_tu' => 9,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Phụ kiện công nghệ',
                'slug' => 'phu-kien-cong-nghe',
                'mo_ta' => 'Tai nghe, bàn phím, chuột, sạc dự phòng',
                'hinh_anh' => 'https://photo2.tinhte.vn/data/attachment-files/2024/11/8553793_top-phu-kien-cong-nghe-can-mang-theo-khi-di-du-lich.png',
                'thu_tu' => 10,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Nội thất',
                'slug' => 'noi-that',
                'mo_ta' => 'Ghế, bàn, tủ, giường, kệ gỗ',
                'hinh_anh' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511',
                'thu_tu' => 11,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Xe & Phương tiện',
                'slug' => 'xe',
                'mo_ta' => 'Xe đạp, xe máy cũ, phụ tùng',
                'hinh_anh' => 'https://images.unsplash.com/photo-1516466723877-e4ec1d736c8a',
                'thu_tu' => 12,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Thể thao',
                'slug' => 'the-thao',
                'mo_ta' => 'Dụng cụ thể thao, giày bóng đá, vợt cầu lông',
                'hinh_anh' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b',
                'thu_tu' => 13,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Mẹ & Bé',
                'slug' => 'me-va-be',
                'mo_ta' => 'Đồ trẻ em, xe đẩy, sữa bột',
                'hinh_anh' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994',
                'thu_tu' => 14,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Đồ chơi',
                'slug' => 'do-choi',
                'mo_ta' => 'Lego, mô hình, thú nhồi bông',
                'hinh_anh' => 'https://images.unsplash.com/photo-1596464716127-f2a82984de30',
                'thu_tu' => 15,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Nhạc cụ',
                'slug' => 'nhac-cu',
                'mo_ta' => 'Guitar, piano, ukulele',
                'hinh_anh' => 'https://images.unsplash.com/photo-1485579149621-3123dd979885',
                'thu_tu' => 16,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Đồ văn phòng',
                'slug' => 'van-phong-pham',
                'mo_ta' => 'Bút, giấy, máy in, ghế xoay',
                'hinh_anh' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b',
                'thu_tu' => 17,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Thú cưng',
                'slug' => 'thu-cung',
                'mo_ta' => 'Đồ ăn, đồ chơi, phụ kiện thú cưng',
                'hinh_anh' => 'https://images.unsplash.com/photo-1507146426996-ef05306b995a',
                'thu_tu' => 18,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Mỹ phẩm',
                'slug' => 'my-pham',
                'mo_ta' => 'Kem dưỡng, sữa rửa mặt, nước hoa',
                'hinh_anh' => 'https://images.unsplash.com/photo-1563170351-be82bc888aa4',
                'thu_tu' => 19,
                'is_active' => true,
            ],
            [
                'ten_danh_muc' => 'Đồ sưu tầm',
                'slug' => 'do-suu-tam',
                'mo_ta' => 'Mô hình, tiền xu, tượng, vật phẩm cổ',
                'hinh_anh' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03',
                'thu_tu' => 20,
                'is_active' => true,
            ],
        ];


        foreach ($danhMucs as $danhMuc) {
            DB::table('danh_mucs')->insert([
                'ten_danh_muc' => $danhMuc['ten_danh_muc'],
                'slug' => $danhMuc['slug'],
                'mo_ta' => $danhMuc['mo_ta'],
                'hinh_anh' => $danhMuc['hinh_anh'],
                'thu_tu' => $danhMuc['thu_tu'],
                'is_active' => $danhMuc['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
