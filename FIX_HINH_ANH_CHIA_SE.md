# Fix: Hình ảnh không hiển thị khi chia sẻ qua mạng

## Vấn đề
- Hình ảnh hiển thị trên máy của bạn nhưng không hiển thị trên máy người khác khi truy cập qua link chia sẻ
- Nguyên nhân: Backend đang lưu absolute URL với `localhost` (ví dụ: `http://127.0.0.1:8000/storage/products/image.jpg`)
- Khi máy khác truy cập, URL này không hoạt động vì trỏ về localhost của máy server

## Giải pháp đã áp dụng

### 1. Backend: Lưu relative path thay vì absolute URL
- **File**: `BE_Second-hand-Goods-Trading-Platform/app/Http/Controllers/SanPhamController.php`
- **Thay đổi**: Lưu `/storage/products/filename.jpg` thay vì `http://127.0.0.1:8000/storage/products/filename.jpg`
- **Áp dụng cho**: 
  - `storeClient()` - Đăng bán sản phẩm
  - `updateSellerProduct()` - Cập nhật sản phẩm

### 2. Backend: Tự động convert relative path thành absolute URL khi trả về API
- **File**: `BE_Second-hand-Goods-Trading-Platform/app/Models/SanPham.php`
- **Thay đổi**: 
  - Thêm method `convertPathToUrl()` để convert relative path thành absolute URL dựa trên request hiện tại
  - Cập nhật `getImagesArray()` để tự động convert tất cả relative paths thành absolute URLs
- **Cách hoạt động**: 
  - Lấy scheme (http/https), host, và port từ request hiện tại
  - Tạo absolute URL: `{scheme}://{host}:{port}/storage/products/image.jpg`
  - Nếu không có request, fallback về `APP_URL` từ config

### 3. Backend: Cập nhật normalizeHinhAnh()
- **File**: `BE_Second-hand-Goods-Trading-Platform/app/Models/SanPham.php`
- **Thay đổi**: Giữ nguyên relative path khi normalize, không convert thành absolute URL

## Cách test

1. **Chạy Backend với host 0.0.0.0**:
   ```bash
   cd BE_Second-hand-Goods-Trading-Platform
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Chạy Frontend với host 0.0.0.0**:
   ```bash
   cd FE_Second-hand-Goods-Trading-Platform
   npm run dev -- --host 0.0.0.0
   ```

3. **Lấy IP của máy bạn**:
   - Windows: `ipconfig` → tìm IPv4 Address
   - Linux/Mac: `ifconfig` hoặc `ip addr`

4. **Truy cập từ máy khác**:
   - Mở trình duyệt trên máy khác
   - Truy cập: `http://{IP_CUA_BAN}:5173`
   - Đăng nhập và thử xem sản phẩm có hình ảnh

5. **Kiểm tra**:
   - Hình ảnh sản phẩm hiển thị đúng
   - URL hình ảnh trong Network tab của DevTools phải là: `http://{IP_CUA_BAN}:8000/storage/products/...`
   - Không phải `http://127.0.0.1:8000/...` hoặc `http://localhost:8000/...`

## Lưu ý

- **Storage link**: Đảm bảo đã chạy `php artisan storage:link` để tạo symbolic link từ `public/storage` → `storage/app/public`
- **Firewall**: Mở port 8000 (Backend) và 5173 (Frontend) trong firewall nếu cần
- **CORS**: Đã cấu hình CORS để cho phép requests từ mọi origin (xem `BE_Second-hand-Goods-Trading-Platform/config/cors.php`)

## Nếu vẫn không hoạt động

1. **Kiểm tra storage link**:
   ```bash
   cd BE_Second-hand-Goods-Trading-Platform
   php artisan storage:link
   ls -la public/storage  # Kiểm tra link đã tạo
   ```

2. **Kiểm tra quyền truy cập file**:
   - Đảm bảo thư mục `storage/app/public/products` có quyền đọc
   - Windows: Kiểm tra Properties → Security
   - Linux/Mac: `chmod -R 755 storage/app/public`

3. **Kiểm tra URL trong database**:
   ```sql
   SELECT id, ten_san_pham, hinh_anh FROM san_phams LIMIT 5;
   ```
   - `hinh_anh` phải là relative path: `["/storage/products/..."]`
   - Không phải absolute URL: `["http://127.0.0.1:8000/storage/products/..."]`

4. **Kiểm tra Network tab trong DevTools**:
   - Mở DevTools (F12) → Network tab
   - Reload trang và xem requests hình ảnh
   - Kiểm tra URL và status code (phải là 200)

## Files đã thay đổi

1. `BE_Second-hand-Goods-Trading-Platform/app/Models/SanPham.php`
   - Thêm method `convertPathToUrl()`
   - Cập nhật `getImagesArray()` để convert paths
   - Cập nhật `normalizeHinhAnh()` để giữ relative paths

2. `BE_Second-hand-Goods-Trading-Platform/app/Http/Controllers/SanPhamController.php`
   - Sửa `storeClient()` để lưu relative paths
   - Sửa `updateSellerProduct()` để lưu relative paths

