# Hướng dẫn: Đổi URL từ localhost sang IP mạng

## Vấn đề
- URL hình ảnh đang dùng `http://127.0.0.1:8000` không thể truy cập từ máy khác
- Cần đổi sang IP mạng để chia sẻ được

## Giải pháp

### Bước 1: Lấy IP mạng của máy bạn

**Windows:**
```bash
ipconfig | findstr /i "IPv4"
```

**Linux/Mac:**
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
# hoặc
ip addr | grep "inet " | grep -v 127.0.0.1
```

**Ví dụ IP của bạn:** `192.168.1.127`

### Bước 2: Cấu hình trong file `.env`

Mở file `.env` trong thư mục `BE_Second-hand-Goods-Trading-Platform`:

```env
# Thay đổi dòng này
APP_URL=http://127.0.0.1:8000

# Thành IP mạng của bạn (ví dụ)
APP_URL=http://192.168.1.127:8000
```

### Bước 3: Clear cache Laravel

Sau khi thay đổi `.env`, chạy lệnh:

```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan config:clear
php artisan cache:clear
```

### Bước 4: Chạy server với host 0.0.0.0

**Quan trọng:** Phải chạy với `--host=0.0.0.0` để cho phép truy cập từ mạng:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**KHÔNG dùng:**
```bash
php artisan serve  # Chỉ cho phép localhost
```

### Bước 5: Kiểm tra

1. **Từ máy của bạn:**
   - Truy cập: `http://192.168.1.127:8000/api/client/san-pham`
   - Kiểm tra URL hình ảnh trong response phải là: `http://192.168.1.127:8000/storage/products/...`

2. **Từ máy khác trong cùng mạng:**
   - Truy cập: `http://192.168.1.127:8000/api/client/san-pham`
   - Hình ảnh phải hiển thị được

## Cách hoạt động

Code đã được cập nhật để:
1. **Ưu tiên 1**: Lấy host từ request hiện tại (tự động detect IP khi truy cập qua IP)
2. **Ưu tiên 2**: Nếu host là `localhost` hoặc `127.0.0.1`, tự động lấy IP từ `APP_URL` trong `.env`
3. **Fallback**: Dùng `APP_URL` từ config

## Lưu ý

1. **Firewall**: Đảm bảo port 8000 đã được mở trong Windows Firewall
2. **IP thay đổi**: Nếu IP mạng thay đổi (khi đổi WiFi), cần cập nhật lại `APP_URL` trong `.env`
3. **Chạy với 0.0.0.0**: Luôn chạy `php artisan serve --host=0.0.0.0` khi muốn chia sẻ qua mạng

## Ví dụ cấu hình đầy đủ

```env
# Backend URL - Dùng IP mạng
APP_URL=http://192.168.1.127:8000

# Frontend URL (nếu cần)
FRONTEND_URL=http://192.168.1.127:5173

# VNPay Return URL (nếu dùng VNPay)
VNPAY_RETURN_URL=http://192.168.1.127:8000/api/client/payment/vnpay/callback
```

## Troubleshooting

### Hình ảnh vẫn không hiển thị từ máy khác

1. **Kiểm tra APP_URL:**
   ```bash
   php artisan tinker
   >>> config('app.url')
   ```
   Phải trả về IP mạng, không phải `127.0.0.1`

2. **Kiểm tra server đang chạy:**
   ```bash
   # Phải thấy: Listening on http://0.0.0.0:8000
   # KHÔNG phải: Listening on http://127.0.0.1:8000
   ```

3. **Kiểm tra firewall:**
   - Windows: Control Panel → Windows Defender Firewall → Advanced Settings
   - Thêm rule cho port 8000 (Inbound Rules)

4. **Test từ máy khác:**
   ```bash
   # Từ máy khác, chạy:
   curl http://192.168.1.127:8000/api/client/san-pham
   ```
   Nếu không kết nối được, kiểm tra firewall hoặc network

## Tự động detect IP (Tùy chọn)

Nếu muốn tự động detect IP mà không cần sửa `.env` mỗi lần, có thể tạo script:

**Windows (detect_ip.bat):**
```batch
@echo off
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    set IP=%%a
    goto :found
)
:found
set IP=%IP:~1%
echo APP_URL=http://%IP%:8000
```

**Linux/Mac (detect_ip.sh):**
```bash
#!/bin/bash
IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | head -1)
echo "APP_URL=http://$IP:8000"
```



