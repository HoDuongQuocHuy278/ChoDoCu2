# 📁 CẤU TRÚC THƯ MỤC DỰ ÁN

## 🎨 FRONTEND - FE_Second-hand-Goods-Trading-Platform

### 📂 Cấu trúc tổng quan
```
FE_Second-hand-Goods-Trading-Platform/
├── public/                    # Tài nguyên tĩnh
│   └── vite.svg
├── src/                      # Source code chính
│   ├── App.vue              # Component gốc
│   ├── main.js              # Entry point
│   ├── style.css            # Global styles
│   │
│   ├── assets/              # Tài nguyên (CSS, JS, images, fonts)
│   │   ├── css/             # Stylesheets
│   │   │   ├── app.css
│   │   │   ├── bootstrap.css
│   │   │   ├── components/  # Component styles
│   │   │   ├── pages/       # Page-specific styles
│   │   │   └── ...
│   │   ├── js/              # JavaScript libraries
│   │   ├── images/          # Hình ảnh
│   │   ├── fonts/           # Font files
│   │   └── plugins/         # Third-party plugins
│   │
│   ├── components/          # Vue Components
│   │   ├── admin/           # Admin components
│   │   │   ├── QuanLyDonHang/
│   │   │   ├── QuanLyNguoiDung/
│   │   │   └── QuanLySanPham/
│   │   │
│   │   ├── NguoiDangBan/    # Seller components
│   │   │   ├── DangKyBan/          # Đăng ký bán hàng
│   │   │   ├── LichSuBanHang/      # Lịch sử bán hàng
│   │   │   ├── QuanLyDonHang/      # Quản lý đơn hàng
│   │   │   ├── SanPhamCuaToi/      # Sản phẩm của tôi ⭐
│   │   │   ├── Sell/               # Đăng bán sản phẩm
│   │   │   └── TrangBanHang/       # Trang bán hàng
│   │   │
│   │   ├── TrangChu/        # Trang chủ ⭐
│   │   ├── ChiTietSanPham/  # Chi tiết sản phẩm
│   │   ├── ListSanPham/     # Danh sách sản phẩm
│   │   ├── GioHang/         # Giỏ hàng
│   │   ├── Checkout/        # Thanh toán
│   │   ├── DonMua/          # Đơn mua
│   │   ├── DangNhap/        # Đăng nhập
│   │   ├── DangKy/          # Đăng ký
│   │   ├── Profile/         # Hồ sơ người dùng
│   │   ├── chatrealtime/    # Chat realtime
│   │   ├── listchat/        # Danh sách chat
│   │   └── ...
│   │
│   ├── layout/              # Layout components
│   │   ├── components/
│   │   │   ├── BotRocker.vue    # Footer ⭐
│   │   │   ├── TopRocker.vue    # Header
│   │   │   ├── MenuRocker.vue   # Menu
│   │   │   └── ...
│   │   └── wrapper/
│   │       └── index.vue        # Layout wrapper
│   │
│   ├── router/              # Vue Router
│   │   └── index.js         # Route definitions
│   │
│   └── services/            # API services (nếu có)
│
├── package.json             # Dependencies
├── vite.config.js          # Vite configuration
└── index.html              # HTML entry point
```

### 🔑 Các Component quan trọng

#### 1. **TrangChu/index.vue** - Trang chủ
- Hero section
- Danh mục sản phẩm
- Sản phẩm nổi bật
- Chat widget

#### 2. **NguoiDangBan/SanPhamCuaToi/index.vue** - Quản lý sản phẩm
- Danh sách sản phẩm của seller
- Thống kê (doanh thu, đơn hàng, đánh giá)
- Chức năng: Sửa, Xóa, Đổi trạng thái
- Modal chi tiết sản phẩm
- Phân trang

#### 3. **NguoiDangBan/Sell/index.vue** - Đăng bán sản phẩm
- Form đăng sản phẩm mới
- Upload hình ảnh
- Chỉnh sửa sản phẩm

#### 4. **layout/components/BotRocker.vue** - Footer
- Multi-column footer
- Newsletter
- Social links
- Contact info

---

## ⚙️ BACKEND - BE_Second-hand-Goods-Trading-Platform

### 📂 Cấu trúc tổng quan
```
BE_Second-hand-Goods-Trading-Platform/
├── app/                     # Application code
│   ├── Http/
│   │   ├── Controllers/     # Controllers
│   │   │   ├── SanPhamController.php      # ⭐ Quản lý sản phẩm
│   │   │   ├── KhachHangController.php    # Quản lý khách hàng
│   │   │   ├── DonHangController.php      # Quản lý đơn hàng
│   │   │   ├── DanhMucController.php      # Quản lý danh mục
│   │   │   ├── ChatController.php         # Chat
│   │   │   ├── ThanhToanController.php    # Thanh toán
│   │   │   └── ...
│   │   │
│   │   ├── Middleware/      # Middleware
│   │   │   ├── CheckRole.php
│   │   │   └── JsonResponseMiddleware.php
│   │   │
│   │   └── Requests/        # Form Requests
│   │       ├── StoreSanPhamRequest.php
│   │       └── ...
│   │
│   ├── Models/              # Eloquent Models
│   │   ├── SanPham.php      # ⭐ Model sản phẩm
│   │   ├── KhachHang.php    # Model khách hàng
│   │   ├── DonHang.php      # Model đơn hàng
│   │   ├── DanhMuc.php      # Model danh mục
│   │   ├── DanhGia.php      # Model đánh giá
│   │   ├── BinhLuan.php     # Model bình luận
│   │   ├── Chat.php          # Model chat
│   │   ├── ChatMessage.php  # Model tin nhắn
│   │   └── ...
│   │
│   ├── Mail/                # Mail classes
│   │   ├── GuiMaXacNhan.php
│   │   └── MasterMail.php
│   │
│   ├── Casts/               # Custom casts
│   │   └── JsonWithoutEscaping.php
│   │
│   └── Providers/           # Service providers
│       └── AppServiceProvider.php
│
├── database/
│   ├── migrations/          # Database migrations
│   │   ├── 2025_04_24_074713_create_khach_hangs_table.php
│   │   ├── 2025_10_26_070642_create_danh_mucs_table.php
│   │   ├── 2025_11_13_153200_create_san_phams_table.php
│   │   ├── 2025_11_13_153305_create_don_hangs_table.php
│   │   ├── 2025_11_17_000000_create_chats_table.php
│   │   ├── 2025_11_21_165139_convert_hinh_anh_to_json_array_format.php
│   │   └── ...
│   │
│   └── seeders/             # Database seeders
│       ├── DatabaseSeeder.php
│       ├── KhachHangSeeder.php
│       ├── SanPhamSeeder.php
│       ├── DanhMucSeeder.php
│       └── ...
│
├── routes/
│   ├── api.php              # ⭐ API routes
│   ├── web.php              # Web routes (images, etc.)
│   └── console.php          # Console routes
│
├── storage/
│   ├── app/
│   │   └── public/
│   │       └── products/    # ⭐ Hình ảnh sản phẩm
│   └── logs/
│
├── public/
│   ├── storage              # Symlink to storage/app/public
│   └── index.php            # Entry point
│
├── config/                  # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── filesystems.php
│   └── ...
│
├── composer.json            # PHP dependencies
└── artisan                  # Laravel CLI
```

### 🔑 Các File quan trọng

#### 1. **app/Http/Controllers/SanPhamController.php**
- `index()` - Lấy danh sách sản phẩm
- `show()` - Chi tiết sản phẩm
- `storeClient()` - Tạo sản phẩm (client)
- `updateSellerProduct()` - Cập nhật sản phẩm (seller)
- `deleteSellerProduct()` - Xóa sản phẩm
- `getSellerProductStats()` - Thống kê sản phẩm
- `getProductOrders()` - Đơn hàng của sản phẩm
- `getProductReviews()` - Đánh giá sản phẩm

#### 2. **app/Models/SanPham.php**
- `normalizeHinhAnh()` - Chuẩn hóa hình ảnh
- `getImagesArray()` - Lấy mảng hình ảnh
- `getFirstImage()` - Lấy hình đầu tiên
- `setHinhAnhAttribute()` - Accessor cho hinh_anh

#### 3. **routes/api.php**
- `/api/client/san-pham` - CRUD sản phẩm
- `/api/client/seller/san-pham/{id}` - Quản lý sản phẩm seller
- `/api/client/seller/product-stats` - Thống kê
- `/api/client/seller/san-pham/{id}/orders` - Đơn hàng
- `/api/client/seller/san-pham/{id}/reviews` - Đánh giá

#### 4. **routes/web.php**
- `/storage/{path}` - Route phục vụ hình ảnh từ storage

#### 5. **database/migrations/**
- `create_san_phams_table.php` - Bảng sản phẩm
- `convert_hinh_anh_to_json_array_format.php` - Chuyển đổi format hình ảnh
- `normalize_all_hinh_anh_in_database.php` - Chuẩn hóa tất cả hình ảnh

---

## 🔗 Kết nối Frontend - Backend

### API Endpoints chính

#### Sản phẩm
```
GET    /api/client/san-pham              # Danh sách sản phẩm
GET    /api/client/san-pham/{id}         # Chi tiết sản phẩm
POST   /api/client/san-pham              # Tạo sản phẩm
PUT    /api/client/seller/san-pham/{id}  # Cập nhật sản phẩm
DELETE /api/client/seller/san-pham/{id} # Xóa sản phẩm
```

#### Thống kê Seller
```
GET    /api/client/seller/product-stats              # Thống kê tổng quan
GET    /api/client/seller/san-pham/{id}/orders      # Đơn hàng sản phẩm
GET    /api/client/seller/san-pham/{id}/reviews      # Đánh giá sản phẩm
```

#### Hình ảnh
```
GET    /storage/products/{filename}      # Lấy hình ảnh sản phẩm
```

### Authentication
- Sử dụng Laravel Sanctum
- Token lưu trong `localStorage` với key `key_client`
- User info lưu trong `localStorage` với key `user_info`

---

## 📊 Database Schema

### Bảng chính

#### `san_phams`
- `id` - Primary key
- `ten_san_pham` - Tên sản phẩm
- `mo_ta` - Mô tả
- `gia` - Giá (decimal 15,2)
- `hinh_anh` - JSON array string chứa URLs hình ảnh
- `tinh_trang` - Tình trạng (mới, cũ, rất cũ)
- `trang_thai` - Trạng thái (1: đang bán, 3: ẩn)
- `danh_muc_id` - Foreign key đến danh_mucs
- `khach_hang_id` - Foreign key đến khach_hangs (seller)
- `luot_xem` - Lượt xem
- `created_at`, `updated_at`

#### `khach_hangs`
- `id` - Primary key
- `ho_ten` - Họ tên
- `email` - Email
- `so_dien_thoai` - Số điện thoại
- `mat_khau` - Mật khẩu (hashed)
- `is_seller` - Có phải seller không
- `role` - Vai trò (admin, user)
- `trang_thai` - Trạng thái tài khoản
- `created_at`, `updated_at`

#### `don_hangs`
- `id` - Primary key
- `khach_hang_id` - Người mua
- `san_pham_id` - Sản phẩm
- `so_luong` - Số lượng
- `tong_tien` - Tổng tiền
- `trang_thai` - Trạng thái đơn hàng
- `created_at`, `updated_at`

---

## 🛠️ Công nghệ sử dụng

### Frontend
- **Vue.js 3** - Framework
- **Vue Router** - Routing
- **Axios** - HTTP client
- **Vite** - Build tool
- **Bootstrap** - CSS framework

### Backend
- **Laravel** - PHP framework
- **Laravel Sanctum** - Authentication
- **MySQL** - Database
- **File Storage** - Lưu trữ hình ảnh

---

## 📝 Ghi chú

- ⭐ Đánh dấu các file/component quan trọng đã được chỉnh sửa gần đây
- Hình ảnh sản phẩm được lưu trong `storage/app/public/products/`
- Symlink `public/storage` → `storage/app/public`
- API base URL: `http://127.0.0.1:8000/api/client`
- Frontend dev server: Thường chạy trên port 5173 (Vite)

