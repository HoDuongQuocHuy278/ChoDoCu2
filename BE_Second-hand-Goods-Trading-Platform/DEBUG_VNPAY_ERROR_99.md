# Hướng dẫn Debug Lỗi VNPAY Code 99

## Lỗi Code 99 là gì?

Lỗi **Code 99** của VNPAY có nghĩa là "Lỗi không xác định". Thường do:

1. **Chữ ký (Hash) không hợp lệ** - HashSecret không đúng
2. **Terminal chưa được phê duyệt** - TmnCode không đúng hoặc chưa kích hoạt
3. **Return URL không hợp lệ** - URL không accessible từ internet
4. **Thiếu thông tin bắt buộc** - Thiếu các trường bắt buộc trong request

## Các bước kiểm tra

### 1. Kiểm tra cấu hình trong `.env`

```bash
# Kiểm tra các giá trị sau:
VNPAY_TMN_CODE=MUAP6QM1
VNPAY_HASH_SECRET=QPMYQVFJSIV5UIIC5RF1U8HKPDLHI21D
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://192.168.1.61:8000/api/client/payment/vnpay/callback
```

**Lưu ý:**
- `VNPAY_TMN_CODE` và `VNPAY_HASH_SECRET` phải lấy từ VNPAY Sandbox
- `VNPAY_RETURN_URL` phải là URL có thể truy cập được từ internet (không phải localhost)
- Nếu test local, cần dùng ngrok hoặc IP mạng nội bộ

### 2. Kiểm tra Log

Xem log trong `storage/logs/laravel.log`:

```bash
# Tìm các log liên quan đến VNPAY
tail -f storage/logs/laravel.log | grep -i vnpay
```

Các log quan trọng:
- `VNPayService Initialized` - Kiểm tra cấu hình đã load đúng chưa
- `VNPay Payment URL Created` - Kiểm tra thông tin tạo payment URL
- `VNPAY Callback` - Kiểm tra callback từ VNPAY

### 3. Kiểm tra Return URL

Return URL phải:
- ✅ Accessible từ internet (không phải `127.0.0.1` hoặc `localhost`)
- ✅ Đúng format: `http://your-ip:8000/api/client/payment/vnpay/callback`
- ✅ Route đã được đăng ký đúng

**Test Return URL:**
```bash
# Mở browser và truy cập:
http://192.168.1.61:8000/api/client/payment/vnpay/callback
# Nếu không thấy lỗi 404, URL đã đúng
```

### 4. Kiểm tra thông tin từ VNPAY Sandbox

1. Đăng nhập vào [VNPAY Sandbox](https://sandbox.vnpayment.vn/)
2. Vào **Quản lý Website** → **Thông tin Website**
3. Kiểm tra:
   - **Terminal ID** phải khớp với `VNPAY_TMN_CODE`
   - **Secret Key** phải khớp với `VNPAY_HASH_SECRET`
   - **Return URL** phải được đăng ký trong VNPAY Sandbox

### 5. Kiểm tra Request gửi lên VNPAY

Trong log, tìm dòng `VNPay Payment URL Created` và kiểm tra:
- `tmn_code`: Phải khớp với Terminal ID
- `amount`: Phải > 0 và tính bằng xu (ví dụ: 100000 = 1,000 VND)
- `return_url`: Phải đúng format
- `has_hash`: Phải là `true`

### 6. Restart Laravel Server

Sau khi cập nhật `.env`, **BẮT BUỘC** phải restart server:

```bash
# Dừng server (Ctrl+C)
# Chạy lại:
php artisan serve --host=0.0.0.0 --port=8000
```

## Các lỗi thường gặp và cách sửa

### Lỗi: "VNPAY_TMN_CODE is not configured"
**Nguyên nhân:** Chưa set `VNPAY_TMN_CODE` trong `.env`  
**Giải pháp:** Thêm `VNPAY_TMN_CODE=MUAP6QM1` vào `.env` và restart server

### Lỗi: "VNPAY_HASH_SECRET is not configured"
**Nguyên nhân:** Chưa set `VNPAY_HASH_SECRET` trong `.env`  
**Giải pháp:** Thêm `VNPAY_HASH_SECRET=QPMYQVFJSIV5UIIC5RF1U8HKPDLHI21D` vào `.env` và restart server

### Lỗi: "VNPAY_RETURN_URL is not configured"
**Nguyên nhân:** Chưa set `VNPAY_RETURN_URL` trong `.env`  
**Giải pháp:** Thêm `VNPAY_RETURN_URL=http://192.168.1.61:8000/api/client/payment/vnpay/callback` vào `.env` và restart server

### Lỗi: Return URL không accessible
**Nguyên nhân:** VNPAY không thể truy cập Return URL từ internet  
**Giải pháp:** 
- Nếu test local, dùng ngrok: `ngrok http 8000`
- Hoặc dùng IP mạng nội bộ (như `.:8000`)
- Đảm bảo firewall cho phép kết nối từ internet

### Lỗi: Hash không hợp lệ
**Nguyên nhân:** `VNPAY_HASH_SECRET` không đúng  
**Giải pháp:** 
- Kiểm tra lại Secret Key từ VNPAY Sandbox
- Đảm bảo không có khoảng trắng thừa trong `.env`
- Restart server sau khi sửa

## Test lại

1. **Xóa cache config:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Restart server:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

3. **Test thanh toán:**
   - Tạo đơn hàng từ Frontend
   - Chọn thanh toán VNPAY
   - Kiểm tra log để xem có lỗi gì không

## Liên hệ hỗ trợ

Nếu vẫn gặp lỗi sau khi kiểm tra tất cả các bước trên:

- **Email VNPAY:** hotrovnpay@vnpay.vn
- **Website:** https://sandbox.vnpayment.vn/
- **Documentation:** https://sandbox.vnpayment.vn/apis/docs/

**Lưu ý:** Lỗi JavaScript trên trang VNPAY (`timer is not defined`) là lỗi của VNPAY, không phải lỗi của code. Nó không ảnh hưởng đến chức năng thanh toán.



