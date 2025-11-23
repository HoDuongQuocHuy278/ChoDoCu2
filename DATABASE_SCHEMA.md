# 📊 CƠ SỞ DỮ LIỆU - DATABASE SCHEMA

> Tài liệu mô tả đầy đủ cấu trúc cơ sở dữ liệu của hệ thống Chợ Đồ Cũ

---

## 📋 MỤC LỤC

1. [Tổng quan](#-tổng-quan)
2. [Sơ đồ quan hệ (ERD)](#-sơ-đồ-quan-hệ-erd)
3. [Chi tiết các bảng](#-chi-tiết-các-bảng)
4. [Indexes và Performance](#-indexes-và-performance)
5. [Relationships](#-relationships)

---

## 🎯 TỔNG QUAN

Hệ thống sử dụng **MySQL/MariaDB** với **Laravel Eloquent ORM**.

### Thống kê:
- **Tổng số bảng**: 11 bảng chính
- **Bảng hệ thống**: 3 bảng (users, cache, jobs, personal_access_tokens)
- **Bảng nghiệp vụ**: 8 bảng

### Các bảng chính:
1. `khach_hangs` - Khách hàng/Người dùng
2. `danh_mucs` - Danh mục sản phẩm
3. `san_phams` - Sản phẩm
4. `don_hangs` - Đơn hàng
5. `danh_gias` - Đánh giá sản phẩm
6. `binh_luans` - Bình luận
7. `chats` - Phòng chat
8. `chat_messages` - Tin nhắn chat
9. `notifications` - Thông báo
10. `bai_viets` - Bài viết/Blog

---

## 🔗 SƠ ĐỒ QUAN HỆ (ERD)

```
khach_hangs (1) ──┬── (N) san_phams
                  ├── (N) don_hangs (buyer)
                  ├── (N) don_hangs (seller via san_pham)
                  ├── (N) danh_gias
                  ├── (N) binh_luans
                  ├── (N) chats (user1)
                  ├── (N) chats (user2)
                  ├── (N) chat_messages
                  ├── (N) notifications
                  └── (N) bai_viets

danh_mucs (1) ──── (N) san_phams

san_phams (1) ──── (N) don_hangs
              ├── (N) danh_gias
              ├── (N) binh_luans
              └── (N) chats

binh_luans (1) ──── (N) binh_luans (self-referencing: binh_luan_cha_id)

chats (1) ──────── (N) chat_messages
```

---

## 📊 CHI TIẾT CÁC BẢNG

### 1. Bảng `khach_hangs` (Khách hàng/Người dùng)

**Mô tả**: Lưu thông tin tất cả người dùng hệ thống (buyer, seller, admin)

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID khách hàng |
| `ho_va_ten` | VARCHAR(255) | NOT NULL | Họ và tên |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | Email đăng nhập |
| `so_dien_thoai` | VARCHAR(255) | NULL | Số điện thoại |
| `password` | VARCHAR(255) | NOT NULL | Mật khẩu (hashed) |
| `cccd` | VARCHAR(255) | NULL, UNIQUE | Số CCCD/CMND |
| `ngay_sinh` | DATE | NULL | Ngày sinh |
| `is_seller` | INTEGER | DEFAULT 0 | 0: chưa đăng ký bán, 1: đã đăng ký bán |
| `hash_reset` | VARCHAR(255) | NULL | Hash để reset mật khẩu |
| `hash_active` | VARCHAR(255) | NULL | Hash để kích hoạt tài khoản |
| `is_active` | INTEGER | DEFAULT 0 | 0: chưa kích hoạt, 1: đã kích hoạt |
| `is_block` | INTEGER | DEFAULT 0 | 0: chưa bị khóa, 1: đã bị khóa |
| `ten_ngan_hang` | VARCHAR(255) | NULL | Tên ngân hàng (cho seller) |
| `so_tai_khoan` | VARCHAR(255) | NULL | Số tài khoản ngân hàng |
| `chu_tai_khoan` | VARCHAR(255) | NULL | Chủ tài khoản |
| `dia_chi_ho_ten` | VARCHAR(255) | NULL | Họ tên người nhận (địa chỉ) |
| `dia_chi_so_dien_thoai` | VARCHAR(255) | NULL | SĐT người nhận |
| `dia_chi_chi_tiet` | TEXT | NULL | Địa chỉ chi tiết |
| `gioi_tinh` | VARCHAR(255) | NULL | Giới tính |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Indexes**:
- PRIMARY KEY: `id`
- UNIQUE: `email`
- UNIQUE: `cccd`

---

### 2. Bảng `danh_mucs` (Danh mục sản phẩm)

**Mô tả**: Phân loại danh mục sản phẩm

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID danh mục |
| `ten_danh_muc` | VARCHAR(255) | NOT NULL | Tên danh mục |
| `slug` | VARCHAR(255) | NOT NULL, UNIQUE | URL-friendly name |
| `mo_ta` | VARCHAR(255) | NULL | Mô tả ngắn |
| `hinh_anh` | VARCHAR(255) | NULL | Hình ảnh danh mục |
| `thu_tu` | INTEGER | DEFAULT 0 | Thứ tự hiển thị |
| `is_active` | BOOLEAN | DEFAULT TRUE | Trạng thái hoạt động |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Indexes**:
- PRIMARY KEY: `id`
- UNIQUE: `slug`

---

### 3. Bảng `san_phams` (Sản phẩm)

**Mô tả**: Thông tin sản phẩm được đăng bán

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID sản phẩm |
| `ten_san_pham` | VARCHAR(255) | NOT NULL | Tên sản phẩm |
| `mo_ta` | TEXT | NULL | Mô tả chi tiết |
| `gia` | DECIMAL(15,2) | NOT NULL | Giá sản phẩm |
| `hinh_anh` | VARCHAR(255) | NULL | JSON array string chứa URLs hình ảnh |
| `tinh_trang` | VARCHAR(255) | DEFAULT 'moi' | Tình trạng: 'moi', 'cu', 'rat_cu' |
| `category` | VARCHAR(255) | NULL | Category (deprecated, dùng danh_muc_id) |
| `danh_muc_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY | ID danh mục |
| `thuong_hieu` | VARCHAR(255) | NULL | Thương hiệu |
| `mau_sac` | VARCHAR(255) | NULL | Màu sắc |
| `kich_thuoc` | VARCHAR(255) | NULL | Kích thước |
| `dia_chi` | TEXT | NULL | Địa chỉ bán hàng |
| `tinh_thanh` | VARCHAR(255) | NULL | Tỉnh/Thành phố |
| `quan_huyen` | VARCHAR(255) | NULL | Quận/Huyện |
| `khach_hang_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người bán (seller) |
| `trang_thai` | INTEGER | DEFAULT 1 | 1: đang bán, 2: đã bán, 3: đã ẩn |
| `luot_xem` | INTEGER | DEFAULT 0 | Lượt xem sản phẩm |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `danh_muc_id` → `danh_mucs.id` (ON DELETE SET NULL)
- `khach_hang_id` → `khach_hangs.id` (ON DELETE CASCADE)

**Indexes**:
- PRIMARY KEY: `id`
- INDEX: `khach_hang_id` (san_phams_khach_hang_id_index)
- INDEX: `danh_muc_id` (san_phams_danh_muc_id_index)
- INDEX: `trang_thai` (san_phams_trang_thai_index)
- INDEX: `created_at` (san_phams_created_at_index)
- COMPOSITE INDEX: `(gia, trang_thai)` (san_phams_gia_trang_thai_index)

**Lưu ý**: 
- `hinh_anh` lưu dạng JSON array string: `["url1", "url2", ...]`
- Format: `["http://domain.com/storage/products/image1.jpg", ...]`

---

### 4. Bảng `don_hangs` (Đơn hàng)

**Mô tả**: Thông tin đơn hàng mua bán

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID đơn hàng |
| `ma_don_hang` | VARCHAR(20) | NOT NULL, UNIQUE | Mã đơn hàng (unique) |
| `san_pham_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID sản phẩm |
| `khach_hang_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY | ID người mua (có thể NULL nếu mua không đăng nhập) |
| `so_luong` | INTEGER UNSIGNED | DEFAULT 1 | Số lượng mua |
| `tong_tien` | DECIMAL(12,2) | NOT NULL | Tổng tiền đơn hàng |
| `buyer_name` | VARCHAR(255) | NOT NULL | Tên người mua |
| `buyer_email` | VARCHAR(255) | NULL | Email người mua |
| `buyer_phone` | VARCHAR(20) | NULL | SĐT người mua |
| `shipping_address` | TEXT | NULL | Địa chỉ giao hàng |
| `notes` | TEXT | NULL | Ghi chú |
| `payment_method` | VARCHAR(20) | NOT NULL | Phương thức thanh toán |
| `payment_status` | VARCHAR(30) | DEFAULT 'pending' | Trạng thái thanh toán |
| `status` | VARCHAR(30) | DEFAULT 'pending' | Trạng thái đơn hàng |
| `payment_payload` | JSON | NULL | Dữ liệu thanh toán (VNPay response, etc.) |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `san_pham_id` → `san_phams.id` (ON DELETE CASCADE)
- `khach_hang_id` → `khach_hangs.id` (ON DELETE SET NULL)

**Indexes**:
- PRIMARY KEY: `id`
- UNIQUE: `ma_don_hang`
- INDEX: `khach_hang_id` (don_hangs_khach_hang_id_index)
- INDEX: `san_pham_id` (don_hangs_san_pham_id_index)
- INDEX: `status` (don_hangs_status_index)
- INDEX: `payment_status` (don_hangs_payment_status_index)
- INDEX: `buyer_email` (don_hangs_buyer_email_index)
- INDEX: `buyer_phone` (don_hangs_buyer_phone_index)
- COMPOSITE INDEX: `(created_at, status)` (don_hangs_created_at_status_index)

**Trạng thái đơn hàng** (`status`):
- `pending` - Chờ xử lý
- `confirmed` - Đã xác nhận
- `processing` - Đang xử lý
- `shipping` - Đang giao hàng
- `completed` - Hoàn thành
- `cancelled` - Đã hủy

**Trạng thái thanh toán** (`payment_status`):
- `pending` - Chờ thanh toán
- `paid` - Đã thanh toán
- `failed` - Thanh toán thất bại
- `refunded` - Đã hoàn tiền

---

### 5. Bảng `danh_gias` (Đánh giá sản phẩm)

**Mô tả**: Đánh giá và rating sản phẩm từ người mua

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID đánh giá |
| `san_pham_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID sản phẩm |
| `khach_hang_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người đánh giá |
| `diem` | INTEGER | DEFAULT 5 | Điểm đánh giá (1-5 sao) |
| `noi_dung` | TEXT | NULL | Nội dung đánh giá |
| `is_active` | BOOLEAN | DEFAULT TRUE | Trạng thái hiển thị |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `san_pham_id` → `san_phams.id` (ON DELETE CASCADE)
- `khach_hang_id` → `khach_hangs.id` (ON DELETE CASCADE)

**Indexes**:
- PRIMARY KEY: `id`
- INDEX: `san_pham_id` (danh_gias_san_pham_id_index)
- COMPOSITE INDEX: `(san_pham_id, is_active)` (danh_gias_san_pham_id_is_active_index)

---

### 6. Bảng `binh_luans` (Bình luận)

**Mô tả**: Bình luận về sản phẩm (hỗ trợ reply/nested comments)

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID bình luận |
| `san_pham_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID sản phẩm |
| `khach_hang_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người bình luận |
| `noi_dung` | TEXT | NOT NULL | Nội dung bình luận |
| `binh_luan_cha_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY | ID bình luận cha (để reply) |
| `is_active` | BOOLEAN | DEFAULT TRUE | Trạng thái hiển thị |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `san_pham_id` → `san_phams.id` (ON DELETE CASCADE)
- `khach_hang_id` → `khach_hangs.id` (ON DELETE CASCADE)
- `binh_luan_cha_id` → `binh_luans.id` (ON DELETE CASCADE) - Self-referencing

**Indexes**:
- PRIMARY KEY: `id`

**Lưu ý**: Hỗ trợ nested comments (bình luận con) thông qua `binh_luan_cha_id`

---

### 7. Bảng `chats` (Phòng chat)

**Mô tả**: Phòng chat giữa 2 người dùng (có thể liên quan đến sản phẩm)

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID phòng chat |
| `user1_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người dùng 1 |
| `user2_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người dùng 2 |
| `san_pham_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY | ID sản phẩm (nếu chat về sản phẩm) |
| `room` | VARCHAR(255) | NOT NULL, UNIQUE | Room identifier: "chat:min_id-max_id" |
| `last_message_at` | TIMESTAMP | NULL | Thời gian tin nhắn cuối |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `user1_id` → `khach_hangs.id` (ON DELETE CASCADE)
- `user2_id` → `khach_hangs.id` (ON DELETE CASCADE)
- `san_pham_id` → `san_phams.id` (ON DELETE SET NULL)

**Indexes**:
- PRIMARY KEY: `id`
- UNIQUE: `room`
- INDEX: `(user1_id, user2_id)` - Composite index
- INDEX: `last_message_at`
- INDEX: `san_pham_id`
- UNIQUE: `(user1_id, user2_id, san_pham_id)` - Prevent duplicate chats

**Lưu ý**: 
- `room` format: `"chat:{min(user1_id, user2_id)}-{max(user1_id, user2_id)}"`
- Đảm bảo không có 2 phòng chat giống nhau giữa cùng 2 người

---

### 8. Bảng `chat_messages` (Tin nhắn chat)

**Mô tả**: Tin nhắn trong phòng chat

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID tin nhắn |
| `chat_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID phòng chat |
| `sender_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người gửi |
| `content` | TEXT | NOT NULL | Nội dung tin nhắn |
| `type` | ENUM | DEFAULT 'text' | Loại: 'text', 'image', 'file' |
| `file_name` | VARCHAR(255) | NULL | Tên file (nếu type = file/image) |
| `file_size` | BIGINT UNSIGNED | NULL | Kích thước file (bytes) |
| `is_read` | BOOLEAN | DEFAULT FALSE | Đã đọc chưa |
| `read_at` | TIMESTAMP | NULL | Thời gian đọc |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `chat_id` → `chats.id` (ON DELETE CASCADE)
- `sender_id` → `khach_hangs.id` (ON DELETE CASCADE)

**Indexes**:
- PRIMARY KEY: `id`
- INDEX: `(chat_id, created_at)` - Composite index để sort messages
- INDEX: `sender_id`
- INDEX: `is_read`

---

### 9. Bảng `notifications` (Thông báo)

**Mô tả**: Thông báo cho người dùng

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID thông báo |
| `khach_hang_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | ID người nhận |
| `type` | VARCHAR(50) | NOT NULL | Loại: 'order', 'message', 'product', 'system' |
| `title` | VARCHAR(255) | NOT NULL | Tiêu đề |
| `message` | TEXT | NOT NULL | Nội dung thông báo |
| `icon` | VARCHAR(50) | NULL | Icon (emoji hoặc icon class) |
| `action_url` | VARCHAR(255) | NULL | URL để chuyển đến khi click |
| `data` | JSON | NULL | Dữ liệu bổ sung |
| `is_read` | BOOLEAN | DEFAULT FALSE | Đã đọc chưa |
| `read_at` | TIMESTAMP | NULL | Thời gian đọc |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `khach_hang_id` → `khach_hangs.id` (ON DELETE CASCADE)

**Indexes**:
- PRIMARY KEY: `id`
- INDEX: `(khach_hang_id, is_read)` - Composite index
- INDEX: `created_at`

---

### 10. Bảng `bai_viets` (Bài viết/Blog)

**Mô tả**: Bài viết/Blog posts

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|--------------|-----------|-------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID bài viết |
| `tieu_de` | VARCHAR(255) | NOT NULL | Tiêu đề bài viết |
| `noi_dung` | TEXT | NOT NULL | Nội dung bài viết |
| `hinh_anh` | VARCHAR(255) | NULL | Hình ảnh đại diện |
| `khach_hang_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY | ID tác giả |
| `is_active` | BOOLEAN | DEFAULT TRUE | Trạng thái hiển thị |
| `luot_xem` | INTEGER | DEFAULT 0 | Lượt xem |
| `created_at` | TIMESTAMP | NULL | Thời gian tạo |
| `updated_at` | TIMESTAMP | NULL | Thời gian cập nhật |

**Foreign Keys**:
- `khach_hang_id` → `khach_hangs.id` (ON DELETE CASCADE)

**Indexes**:
- PRIMARY KEY: `id`

---

## 🔍 INDEXES VÀ PERFORMANCE

### Indexes được tạo để tối ưu hiệu suất:

#### Bảng `san_phams`:
- `khach_hang_id` - Query sản phẩm của seller
- `danh_muc_id` - Filter theo danh mục
- `trang_thai` - Filter theo trạng thái
- `created_at` - Sort theo thời gian
- `(gia, trang_thai)` - Sort và filter giá + trạng thái

#### Bảng `don_hangs`:
- `khach_hang_id` - Query đơn hàng của buyer
- `san_pham_id` - Query đơn hàng của seller
- `status` - Filter theo trạng thái
- `payment_status` - Filter theo trạng thái thanh toán
- `buyer_email`, `buyer_phone` - Match với user
- `(created_at, status)` - Sort và filter

#### Bảng `danh_gias`:
- `san_pham_id` - Query đánh giá sản phẩm
- `(san_pham_id, is_active)` - Filter đánh giá active

#### Bảng `chats`:
- `(user1_id, user2_id)` - Query chat giữa 2 người
- `last_message_at` - Sort theo tin nhắn mới nhất
- `san_pham_id` - Query chat về sản phẩm

#### Bảng `chat_messages`:
- `(chat_id, created_at)` - Sort messages trong chat
- `sender_id` - Query messages của user
- `is_read` - Filter tin nhắn chưa đọc

#### Bảng `notifications`:
- `(khach_hang_id, is_read)` - Query thông báo chưa đọc
- `created_at` - Sort theo thời gian

---

## 🔗 RELATIONSHIPS

### Khách hàng (khach_hangs):
- **hasMany** `san_phams` - Sản phẩm đã đăng
- **hasMany** `don_hangs` (as buyer) - Đơn hàng đã mua
- **hasMany** `danh_gias` - Đánh giá đã viết
- **hasMany** `binh_luans` - Bình luận đã viết
- **hasMany** `chats` (as user1) - Phòng chat (user1)
- **hasMany** `chats` (as user2) - Phòng chat (user2)
- **hasMany** `chat_messages` - Tin nhắn đã gửi
- **hasMany** `notifications` - Thông báo
- **hasMany** `bai_viets` - Bài viết đã viết

### Sản phẩm (san_phams):
- **belongsTo** `khach_hang` (seller) - Người bán
- **belongsTo** `danh_muc` - Danh mục
- **hasMany** `don_hangs` - Đơn hàng
- **hasMany** `danh_gias` - Đánh giá
- **hasMany** `binh_luans` - Bình luận
- **hasMany** `chats` - Phòng chat về sản phẩm

### Đơn hàng (don_hangs):
- **belongsTo** `san_pham` - Sản phẩm
- **belongsTo** `khach_hang` (buyer) - Người mua

### Đánh giá (danh_gias):
- **belongsTo** `san_pham` - Sản phẩm
- **belongsTo** `khach_hang` - Người đánh giá

### Bình luận (binh_luans):
- **belongsTo** `san_pham` - Sản phẩm
- **belongsTo** `khach_hang` - Người bình luận
- **belongsTo** `binh_luan_cha` - Bình luận cha (self-referencing)
- **hasMany** `binh_luan_con` - Bình luận con (self-referencing)

### Chat (chats):
- **belongsTo** `user1` - Người dùng 1
- **belongsTo** `user2` - Người dùng 2
- **belongsTo** `san_pham` - Sản phẩm (nếu có)
- **hasMany** `chat_messages` - Tin nhắn

### Tin nhắn (chat_messages):
- **belongsTo** `chat` - Phòng chat
- **belongsTo** `sender` - Người gửi

### Thông báo (notifications):
- **belongsTo** `khach_hang` - Người nhận

### Bài viết (bai_viets):
- **belongsTo** `khach_hang` - Tác giả

---

## 📝 GHI CHÚ QUAN TRỌNG

### 1. Hình ảnh sản phẩm:
- Lưu dạng **JSON array string** trong cột `hinh_anh`
- Format: `["http://domain.com/storage/products/img1.jpg", "http://domain.com/storage/products/img2.jpg"]`
- Sử dụng `JSON_UNESCAPED_SLASHES` khi encode

### 2. Trạng thái sản phẩm:
- `1` = Đang bán
- `2` = Đã bán
- `3` = Đã ẩn

### 3. Trạng thái đơn hàng:
- `pending` → `confirmed` → `processing` → `shipping` → `completed`
- Hoặc `cancelled` ở bất kỳ giai đoạn nào

### 4. Room identifier (chats):
- Format: `"chat:{min_id}-{max_id}"`
- Ví dụ: `"chat:1-5"` (user1_id=1, user2_id=5)
- Đảm bảo min_id luôn nhỏ hơn max_id

### 5. Soft deletes:
- Hiện tại không sử dụng soft deletes
- Tất cả xóa là hard delete (CASCADE)

---

## 🎯 TỔNG KẾT

### Số lượng bảng: **11 bảng chính**

### Tổng số cột: **~80 cột**

### Foreign Keys: **15 relationships**

### Indexes: **~25 indexes** (bao gồm composite indexes)

### Database Engine: **InnoDB** (mặc định Laravel)

### Charset: **utf8mb4**

### Collation: **utf8mb4_unicode_ci**

---

**Tài liệu này được tạo tự động từ Laravel Migrations**  
**Cập nhật lần cuối**: 2025

