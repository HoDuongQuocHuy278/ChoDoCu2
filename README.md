# 🛒 Second-hand Goods Trading Platform

> Nền tảng thương mại điện tử cho phép người dùng mua bán đồ cũ, trao đổi hàng hóa đã qua sử dụng một cách dễ dàng và an toàn.

[![Vue.js](https://img.shields.io/badge/Vue.js-3.3.4-4FC08D?logo=vue.js)](https://vuejs.org/)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?logo=laravel)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Latest-4479A1?logo=mysql)](https://www.mysql.com/)

## 📋 Mục lục

- [Giới thiệu](#-giới-thiệu)
- [Tính năng](#-tính-năng)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [Cài đặt](#-cài-đặt)
- [Cấu hình](#-cấu-hình)
- [Chạy dự án](#-chạy-dự-án)
- [Cấu trúc dự án](#-cấu-trúc-dự-án)
- [API Documentation](#-api-documentation)
- [Tính năng chi tiết](#-tính-năng-chi-tiết)

- [Đóng góp](#-đóng-góp)
- [License](#-license)

---

## 🎯 Giới thiệu

**Second-hand Goods Trading Platform** là một nền tảng thương mại điện tử hiện đại, được xây dựng để kết nối người mua và người bán các sản phẩm đã qua sử dụng. Dự án sử dụng kiến trúc **Frontend-Backend tách biệt**, với **Vue.js 3** cho giao diện người dùng và **Laravel 12** cho API backend.

### ✨ Điểm nổi bật

- 🎨 **Giao diện hiện đại**: UI/UX được thiết kế đẹp mắt, responsive trên mọi thiết bị
- 🔐 **Bảo mật cao**: Sử dụng Laravel Sanctum cho authentication
- 📱 **Real-time Chat**: Tính năng chat trực tiếp giữa người mua và người bán
- 💳 **Thanh toán**: Tích hợp VNPay cho thanh toán trực tuyến
- 📊 **Thống kê**: Dashboard thống kê chi tiết cho người bán
- 🔍 **Tìm kiếm thông minh**: Tìm kiếm và lọc sản phẩm nâng cao

---

## 🚀 Tính năng

### 👤 Người dùng (Buyer)
- ✅ Đăng ký / Đăng nhập tài khoản
- ✅ Duyệt và tìm kiếm sản phẩm
- ✅ Xem chi tiết sản phẩm
- ✅ Thêm vào giỏ hàng
- ✅ Thanh toán trực tuyến (VNPay)
- ✅ Quản lý đơn mua
- ✅ Chat với người bán
- ✅ Đánh giá sản phẩm
- ✅ Quản lý hồ sơ cá nhân

### 🏪 Người bán (Seller)
- ✅ Đăng ký tài khoản người bán
- ✅ Đăng bán sản phẩm (upload nhiều hình ảnh)
- ✅ Quản lý sản phẩm (Sửa, Xóa, Đổi trạng thái)
- ✅ Xem thống kê bán hàng
- ✅ Quản lý đơn hàng
- ✅ Xem đánh giá và phản hồi
- ✅ Chat với người mua
- ✅ Lịch sử bán hàng

### 👨‍💼 Quản trị viên (Admin)
- ✅ Quản lý người dùng
- ✅ Quản lý sản phẩm
- ✅ Quản lý đơn hàng
- ✅ Xem thống kê tổng quan

---

## 🛠️ Công nghệ sử dụng

### Frontend
| Công nghệ | Phiên bản | Mục đích |
|-----------|-----------|----------|
| **Vue.js** | 3.3.4 | Framework JavaScript |
| **Vue Router** | 4.0.13 | Routing |
| **Axios** | 1.13.2 | HTTP Client |
| **Vite** | 4.4.5 | Build Tool |
| **Bootstrap** | Latest | CSS Framework |

### Backend
| Công nghệ | Phiên bản | Mục đích |
|-----------|-----------|----------|
| **Laravel** | 12.0 | PHP Framework |
| **Laravel Sanctum** | 4.0 | API Authentication |
| **PHP** | 8.2+ | Backend Language |
| **MySQL** | Latest | Database |
| **Composer** | Latest | PHP Dependency Manager |

---

## 💻 Yêu cầu hệ thống

### Frontend
- **Node.js**: >= 16.x
- **npm**: >= 8.x hoặc **yarn**: >= 1.22.x

### Backend
- **PHP**: >= 8.2
- **Composer**: >= 2.x
- **MySQL**: >= 8.0 hoặc **MariaDB**: >= 10.3
- **Apache/Nginx**: Web server
- **OpenSSL PHP Extension**
- **PDO PHP Extension**
- **Mbstring PHP Extension**
- **Tokenizer PHP Extension**
- **XML PHP Extension**
- **Ctype PHP Extension**
- **JSON PHP Extension**

---

## 📦 Cài đặt

### 1. Clone repository

```bash
git clone <repository-url>
cd "Shopee - Copy"
```

### 2. Cài đặt Frontend

```bash
cd FE_Second-hand-Goods-Trading-Platform
npm install
```

### 3. Cài đặt Backend

```bash
cd BE_Second-hand-Goods-Trading-Platform
composer install
```

---

## ⚙️ Cấu hình

### Frontend Configuration

Tạo file `.env` trong thư mục `FE_Second-hand-Goods-Trading-Platform` (nếu chưa có):

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api/client
```

### Backend Configuration

1. **Copy file environment:**

```bash
cd BE_Second-hand-Goods-Trading-Platform
cp .env.example .env
```

2. **Generate application key:**

```bash
php artisan key:generate
```

3. **Cấu hình database trong `.env`:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

APP_URL=http://127.0.0.1:8000
```

4. **Tạo symlink cho storage:**

```bash
php artisan storage:link
```

5. **Chạy migrations và seeders:**

```bash
php artisan migrate:fresh --seed
```

---

## 🏃 Chạy dự án

### Development Mode

#### Frontend (Terminal 1)
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```
Frontend sẽ chạy tại: `http://localhost:5173` (hoặc port khác nếu 5173 đã được sử dụng)

#### Backend (Terminal 2)
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve
```
Backend API sẽ chạy tại: `http://127.0.0.1:8000`

### Production Build

#### Frontend
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run build
```
Files build sẽ được tạo trong thư mục `dist/`

#### Backend
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Cấu trúc dự án

```
Shopee - Copy/
├── FE_Second-hand-Goods-Trading-Platform/    # Frontend (Vue.js)
│   ├── src/
│   │   ├── components/                       # Vue Components
│   │   │   ├── TrangChu/                    # Trang chủ
│   │   │   ├── NguoiDangBan/                # Seller components
│   │   │   │   ├── SanPhamCuaToi/           # Quản lý sản phẩm
│   │   │   │   ├── Sell/                    # Đăng bán
│   │   │   │   └── ...
│   │   │   ├── ChiTietSanPham/              # Chi tiết sản phẩm
│   │   │   ├── GioHang/                     # Giỏ hàng
│   │   │   └── ...
│   │   ├── layout/                           # Layout components
│   │   ├── router/                          # Vue Router
│   │   └── assets/                          # Static assets
│   ├── package.json
│   └── vite.config.js
│
└── BE_Second-hand-Goods-Trading-Platform/    # Backend (Laravel)
    ├── app/
    │   ├── Http/Controllers/                 # Controllers
    │   │   ├── SanPhamController.php        # Quản lý sản phẩm
    │   │   ├── KhachHangController.php      # Quản lý khách hàng
    │   │   └── ...
    │   ├── Models/                           # Eloquent Models
    │   └── ...
    ├── database/
    │   ├── migrations/                       # Database migrations
    │   └── seeders/                         # Database seeders
    ├── routes/
    │   ├── api.php                          # API routes
    │   └── web.php                          # Web routes
    ├── storage/
    │   └── app/public/products/             # Hình ảnh sản phẩm
    └── composer.json
```

> 📖 Xem chi tiết cấu trúc tại [PROJECT_STRUCTURE.md](./PROJECT_STRUCTURE.md)

---

## 📚 API Documentation

### Base URL
```
http://127.0.0.1:8000/api/client
```

### Authentication
API sử dụng **Laravel Sanctum** cho authentication. Gửi token trong header:

```
Authorization: Bearer {token}
```

### Endpoints chính

#### 🔐 Authentication
```
POST   /client/dang-ky              # Đăng ký
POST   /client/dang-nhap            # Đăng nhập
GET    /client/dang-xuat            # Đăng xuất
POST   /client/quen-mat-khau        # Quên mật khẩu
POST   /client/kich-hoat            # Kích hoạt tài khoản
GET    /client/thong-tin            # Thông tin người dùng
```

#### 📦 Sản phẩm
```
GET    /san-pham                    # Danh sách sản phẩm
GET    /san-pham/{id}               # Chi tiết sản phẩm
POST   /san-pham                    # Tạo sản phẩm (Auth)
PUT    /seller/san-pham/{id}       # Cập nhật sản phẩm (Auth)
DELETE /seller/san-pham/{id}       # Xóa sản phẩm (Auth)
```

#### 📊 Thống kê Seller
```
GET    /seller/product-stats                    # Thống kê tổng quan
GET    /seller/san-pham/{id}/orders            # Đơn hàng sản phẩm
GET    /seller/san-pham/{id}/reviews           # Đánh giá sản phẩm
GET    /seller/sales-history                    # Lịch sử bán hàng
```

#### 🛒 Đơn hàng
```
POST   /don-hang                    # Tạo đơn hàng
GET    /don-hang/{id}              # Chi tiết đơn hàng
GET    /don-hang-mua                # Đơn hàng của buyer (Auth)
GET    /don-hang-ban                # Đơn hàng của seller (Auth)
PUT    /don-hang/{id}/trang-thai    # Cập nhật trạng thái (Auth)
```

#### 💬 Chat
```
GET    /chat                        # Danh sách chat (Auth)
GET    /chat/{id}                   # Chi tiết chat (Auth)
POST   /chat                        # Tạo chat mới (Auth)
POST   /chat/{id}/messages          # Gửi tin nhắn (Auth)
```

#### 📸 Hình ảnh
```
GET    /storage/products/{filename} # Lấy hình ảnh sản phẩm
```

### Response Format

**Success:**
```json
{
  "status": true,
  "message": "Thành công",
  "data": { ... }
}
```

**Error:**
```json
{
  "status": false,
  "message": "Lỗi xảy ra",
  "errors": { ... }
}
```

---

## 🎨 Tính năng chi tiết

### 1. Quản lý sản phẩm (Seller)
- ✅ Upload nhiều hình ảnh
- ✅ Chỉnh sửa thông tin sản phẩm
- ✅ Đổi trạng thái (Đang bán / Ẩn)
- ✅ Xóa sản phẩm
- ✅ Xem thống kê chi tiết:
  - Tổng doanh thu
  - Số đơn hàng
  - Đánh giá trung bình
  - Danh sách người mua
  - Lịch sử đánh giá

### 2. Tìm kiếm & Lọc
- ✅ Tìm kiếm theo tên
- ✅ Lọc theo danh mục
- ✅ Sắp xếp theo giá
- ✅ Phân trang

### 3. Thanh toán
- ✅ Tích hợp VNPay
- ✅ Xử lý callback thanh toán
- ✅ Cập nhật trạng thái đơn hàng

### 4. Chat Real-time
- ✅ Chat trực tiếp giữa buyer và seller
- ✅ Lịch sử tin nhắn
- ✅ Thông báo tin nhắn mới

### 5. Đánh giá & Bình luận
- ✅ Đánh giá sản phẩm (1-5 sao)
- ✅ Bình luận sản phẩm
- ✅ Xem đánh giá của người khác

---

## 🤝 Đóng góp

Chúng tôi hoan nghênh mọi đóng góp! Vui lòng làm theo các bước sau:

1. **Fork** repository
2. Tạo **branch** mới (`git checkout -b feature/AmazingFeature`)
3. **Commit** các thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. **Push** lên branch (`git push origin feature/AmazingFeature`)
5. Mở **Pull Request**

### Quy tắc đóng góp
- Tuân thủ code style hiện tại
- Viết commit message rõ ràng
- Thêm comments cho code phức tạp
- Test kỹ trước khi commit

---

## 📝 License

Dự án này được phân phối dưới giấy phép **MIT**. Xem file `LICENSE` để biết thêm chi tiết.

---

## 👥 Tác giả

- **Development Team** - *Initial work*

---

## 🙏 Lời cảm ơn

- Vue.js team cho framework tuyệt vời
- Laravel team cho backend framework mạnh mẽ
- Tất cả các contributors và cộng đồng open source

---

## 📞 Liên hệ

Nếu có bất kỳ câu hỏi hoặc đề xuất nào, vui lòng:
- Mở một [Issue](../../issues)
- Gửi email đến: [your-email@example.com]

---

## 🔗 Liên kết hữu ích

- [Vue.js Documentation](https://vuejs.org/)
- [Laravel Documentation](https://laravel.com/docs)
- [Vue Router Documentation](https://router.vuejs.org/)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

---

<div align="center">
  <p>Made with ❤️ by Development Team</p>
  <p>⭐ Star this repo if you find it helpful!</p>
</div>

