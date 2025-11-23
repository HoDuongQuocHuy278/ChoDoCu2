# ⚠️ QUAN TRỌNG: CHỈ DÙNG VNPAY SANDBOX (TEST)

## Vấn đề

Nếu bạn đang vào trang **thật (production)** của VNPay thay vì trang **test (sandbox)**, có thể do:

1. File `.env` đang có URL production
2. Hoặc có code nào đó hardcode URL production

## Giải pháp

### 1. Kiểm tra file `.env`

Đảm bảo các URL sau đều là **SANDBOX**:

```env
# ✅ ĐÚNG - SANDBOX (TEST)
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_API_URL=https://sandbox.vnpayment.vn/merchant_webapi/api/transaction

# ❌ SAI - PRODUCTION (KHÔNG DÙNG KHI TEST)
# VNPAY_URL=https://www.vnpayment.vn/paymentv2/vpcpay.html
# VNPAY_API_URL=https://www.vnpayment.vn/merchant_webapi/api/transaction
```

### 2. Cách nhận biết

**SANDBOX (Test):**
- URL: `https://sandbox.vnpayment.vn/...`
- Có thể test với tài khoản test
- Không tính tiền thật

**PRODUCTION (Thật):**
- URL: `https://www.vnpayment.vn/...`
- Tính tiền thật
- Cần thông tin merchant thật

### 3. Code đã được bảo vệ

`VNPayService` đã được cập nhật để:
- Tự động chuyển về sandbox nếu phát hiện production URL
- Log cảnh báo nếu có vấn đề
- Đảm bảo chỉ dùng sandbox khi test

### 4. Kiểm tra nhanh

Sau khi cập nhật `.env`, clear cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Xác nhận đang dùng Sandbox

Khi tạo payment URL, kiểm tra log:
- File: `storage/logs/laravel.log`
- Tìm: `VNPayService Initialized`
- Kiểm tra: `is_sandbox` phải là `true`

## Lưu ý

- ✅ **LUÔN dùng sandbox** khi đang phát triển/test
- ❌ **KHÔNG dùng production** trừ khi đã sẵn sàng go-live
- 🔒 **Bảo mật**: Không commit file `.env` với thông tin production


