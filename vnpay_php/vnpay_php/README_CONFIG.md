# Hướng dẫn cấu hình VNPay

## Các bước cấu hình

### 1. Cấu hình file `config.php`

Mở file `config.php` và điền thông tin:

```php
$vnp_TmnCode = "YOUR_TERMINAL_CODE"; // Mã định danh merchant từ VNPay
$vnp_HashSecret = "YOUR_HASH_SECRET"; // Secret key từ VNPay
$vnp_Returnurl = "http://yourdomain.com/vnpay_php/vnpay_return.php"; // URL trả về sau thanh toán
```

### 2. Các URL quan trọng

- **Sandbox (Test)**: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- **Production**: `https://www.vnpayment.vn/paymentv2/vpcpay.html`
- **API Sandbox**: `https://sandbox.vnpayment.vn/merchant_webapi/api/transaction`
- **API Production**: `https://www.vnpayment.vn/merchant_webapi/api/transaction`

### 3. Các file đã được sửa lỗi

#### vnpay_create_payment.php
- ✅ Sửa lỗi toán tử nối chuỗi (từ `+` sang `.`)
- ✅ Thêm validation cho input
- ✅ Thêm kiểm tra số tiền hợp lệ

#### vnpay_return.php
- ✅ Thêm kiểm tra dữ liệu từ VNPay
- ✅ Thêm htmlspecialchars để chống XSS
- ✅ Cải thiện hiển thị số tiền (format VNĐ)
- ✅ Cải thiện thông báo lỗi

#### vnpay_refund.php
- ✅ Thêm validation cho tất cả input
- ✅ Thêm htmlspecialchars để bảo mật
- ✅ Kiểm tra số tiền hợp lệ

#### vnpay_querydr.php
- ✅ Thêm validation cho input
- ✅ Thêm htmlspecialchars để bảo mật

### 4. Lưu ý bảo mật

1. **Không commit file config.php** vào Git với thông tin thật
2. **Kiểm tra chữ ký (SecureHash)** trong mọi request từ VNPay
3. **Validate số tiền** trước khi cập nhật database
4. **Sử dụng HTTPS** trong production
5. **Kiểm tra IPN (vnpay_ipn.php)** để xác nhận giao dịch

### 5. Test thanh toán

1. Truy cập: `http://localhost/vnpay_php/vnpay_php/index.php`
2. Chọn "Giao dịch thanh toán"
3. Nhập số tiền và chọn phương thức thanh toán
4. Sử dụng tài khoản test từ VNPay để thanh toán

### 6. Mã lỗi thường gặp

- **00**: Giao dịch thành công
- **07**: Trừ tiền thành công, giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường)
- **09**: Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking
- **10**: Xác thực thông tin thẻ/tài khoản không đúng quá 3 lần
- **11**: Đã hết hạn chờ thanh toán. Xin vui lòng thực hiện lại giao dịch
- **12**: Thẻ/Tài khoản bị khóa
- **51**: Tài khoản không đủ số dư để thực hiện giao dịch
- **65**: Tài khoản đã vượt quá hạn mức giao dịch trong ngày
- **75**: Ngân hàng thanh toán đang bảo trì
- **99**: Lỗi không xác định



