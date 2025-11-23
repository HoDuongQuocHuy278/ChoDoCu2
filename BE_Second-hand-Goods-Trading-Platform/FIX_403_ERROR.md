# Hướng dẫn sửa lỗi 403 khi truy cập ảnh

## Nguyên nhân lỗi 403:
1. Symlink không hoạt động đúng
2. Route bị chặn bởi middleware
3. Web server (Apache/XAMPP) chặn truy cập
4. Quyền truy cập file không đúng

## Các bước đã thực hiện:

### 1. Cải thiện Route `/storage/{path}`
- Thêm bảo vệ path traversal
- Kiểm tra file tồn tại và quyền truy cập
- Xử lý mime type tốt hơn

### 2. Cập nhật `.htaccess`
- Cho phép serve file từ thư mục storage trực tiếp
- Ưu tiên serve file tĩnh trước khi vào Laravel

### 3. Tạo lại symlink
```bash
php artisan storage:link
```

## Cách kiểm tra:

### 1. Kiểm tra symlink:
```bash
# Windows PowerShell
Get-Item public\storage | Select-Object Target, LinkType

# Hoặc kiểm tra thủ công
# public/storage phải là symlink trỏ đến storage/app/public
```

### 2. Kiểm tra file tồn tại:
```bash
# Kiểm tra file có trong storage
Test-Path "storage\app\public\products\1763479205_a5d5edc7_691c8ea5dbd05.png"
```

### 3. Kiểm tra quyền truy cập:
- Đảm bảo thư mục `storage/app/public` có quyền đọc
- Đảm bảo file có quyền đọc

### 4. Test URL:
```
http://127.0.0.1:8000/storage/products/1763479205_a5d5edc7_691c8ea5dbd05.png
```

## Nếu vẫn lỗi 403:

### Giải pháp 1: Sử dụng route thay vì symlink
Route đã được cải thiện và sẽ serve file qua Laravel.

### Giải pháp 2: Kiểm tra Apache/XAMPP config
Thêm vào `httpd.conf` hoặc virtual host:
```apache
<Directory "C:/xampp/htdocs/Shopee - Copy/BE_Second-hand-Goods-Trading-Platform/public/storage">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Giải pháp 3: Kiểm tra middleware
Đảm bảo route `/storage/{path}` không bị middleware chặn.

## Test nhanh:
1. Mở: `http://127.0.0.1:8000/test-images`
2. Xem `storage_link_exists` phải là `true`
3. Copy một URL từ `files_in_storage` và mở trong trình duyệt





