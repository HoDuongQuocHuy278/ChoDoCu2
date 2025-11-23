# Hướng dẫn kiểm tra ảnh có hiển thị được không

## Cách 1: Kiểm tra qua API Test Route

1. Mở trình duyệt hoặc Postman
2. Truy cập: `http://127.0.0.1:8000/test-images`
3. Xem kết quả JSON để kiểm tra:
   - `storage_info`: Thông tin về thư mục storage
   - `files_in_storage`: Danh sách file ảnh trong storage
   - `products_from_db`: Sản phẩm từ database với URL ảnh

## Cách 2: Kiểm tra trực tiếp URL ảnh

1. Lấy URL ảnh từ API test hoặc từ database
2. Mở URL trong trình duyệt, ví dụ:
   ```
   http://127.0.0.1:8000/storage/products/1763477118_691c867ec9c5d.png
   ```
3. Nếu ảnh hiển thị = OK
4. Nếu 404 hoặc lỗi = Cần kiểm tra lại

## Cách 3: Kiểm tra qua API sản phẩm

1. Gọi API: `GET http://127.0.0.1:8000/api/client/san-pham`
2. Xem field `image` hoặc `images` trong response
3. Kiểm tra URL không có dấu `\` (ví dụ: không có `http:\/\/`)
4. Copy URL và mở trong trình duyệt

## Cách 4: Kiểm tra trong Frontend

1. Mở Developer Tools (F12)
2. Vào tab Network
3. Tải lại trang có ảnh
4. Xem request ảnh:
   - Status 200 = OK
   - Status 404 = File không tồn tại
   - Status 403 = Không có quyền truy cập

## Cách 5: Kiểm tra bằng Artisan Command

Chạy lệnh:
```bash
php artisan tinker
```

Sau đó:
```php
// Kiểm tra sản phẩm
$sp = App\Models\SanPham::first();
echo $sp->first_image;

// Kiểm tra file tồn tại
$path = storage_path('app/public/products/1763477118_691c867ec9c5d.png');
echo file_exists($path) ? 'File exists' : 'File not found';

// Kiểm tra symlink
echo is_link(public_path('storage')) ? 'Symlink OK' : 'Symlink missing';
```

## Các vấn đề thường gặp:

1. **Symlink chưa tạo**: Chạy `php artisan storage:link`
2. **File không tồn tại**: Kiểm tra file có trong `storage/app/public/products/`
3. **URL bị escape**: Kiểm tra middleware `JsonResponseMiddleware` đã hoạt động
4. **Quyền truy cập**: Kiểm tra quyền thư mục `storage/app/public`

## Quick Test Commands:

```bash
# Kiểm tra symlink
php artisan storage:link

# Xóa cache
php artisan optimize:clear

# Kiểm tra route
php artisan route:list | grep test-images
```





