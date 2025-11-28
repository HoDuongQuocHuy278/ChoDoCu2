# Hướng dẫn lấy thông tin test VNPay Sandbox

## Lỗi Code 71: "The terminal (website) not approved"

Lỗi này xảy ra khi:
- Chưa điền `$vnp_TmnCode` và `$vnp_HashSecret` trong file `config.php`
- Hoặc điền sai thông tin test từ VNPay Sandbox

## Các bước lấy thông tin test

### Bước 1: Truy cập VNPay Sandbox
- URL: https://sandbox.vnpayment.vn/
- Đăng ký tài khoản test (miễn phí) hoặc đăng nhập nếu đã có

### Bước 2: Lấy thông tin Terminal Code và Hash Secret

Sau khi đăng nhập vào VNPay Sandbox:

1. Vào phần **"Thông tin kết nối"** hoặc **"Integration"**
2. Tìm các thông tin sau:
   - **Terminal Code (TmnCode)**: Mã định danh merchant
     - Ví dụ: `2QXUI4J4`
   - **Hash Secret**: Secret key để tạo chữ ký
     - Ví dụ: `RAOCTKRWJDKODSJDISJSIWOWOWOWOW`

### Bước 3: Cấu hình file config.php

Mở file `vnpay_php/vnpay_php/config.php` và điền thông tin:

```php
$vnp_TmnCode = "2QXUI4J4"; // Điền Terminal Code của bạn
$vnp_HashSecret = "RAOCTKRWJDKODSJIWOWOWOWOWOWOWOW"; // Điền Hash Secret của bạn
```

### Bước 4: Cập nhật Return URL (nếu cần)

Nếu bạn chạy trên domain khác localhost, cập nhật:

```php
$vnp_Returnurl = "http://yourdomain.com/vnpay_php/vnpay_php/vnpay_return.php";
```

## Test thanh toán

1. Truy cập: `http://localhost/vnpay_php/vnpay_php/index.php`
2. Chọn "Giao dịch thanh toán"
3. Nhập số tiền (ví dụ: 10000)
4. Chọn phương thức thanh toán
5. Click "Thanh toán"

## Tài khoản test VNPay

Khi thanh toán trên VNPay Sandbox, bạn có thể sử dụng:

- **Thẻ test**: VNPay sẽ cung cấp số thẻ test
- **Tài khoản test**: Đăng nhập bằng tài khoản test đã tạo

## Lưu ý quan trọng

1. ✅ **CHỈ dùng Sandbox URL** cho test:
   - `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`

2. ❌ **KHÔNG dùng Production URL** khi test:
   - `https://www.vnpayment.vn/paymentv2/vpcpay.html`

3. 🔒 **Bảo mật**: Không commit file `config.php` với thông tin thật lên Git

4. 🧪 **Test environment**: Tất cả giao dịch trên Sandbox là giả lập, không thật

## Mã lỗi thường gặp

- **Code 71**: Terminal chưa được phê duyệt → Kiểm tra TmnCode và HashSecret
- **Code 97**: Chữ ký không hợp lệ → Kiểm tra HashSecret
- **Code 99**: Lỗi không xác định → Kiểm tra lại cấu hình

## Liên hệ hỗ trợ

- Email: hotrovnpay@vnpay.vn
- Website: https://sandbox.vnpayment.vn/
- Documentation: https://sandbox.vnpayment.vn/apis/docs/





