# Hướng dẫn Kiểm tra Thanh toán VNPAY và MBBank

## 📋 Tổng quan

Hệ thống hỗ trợ 2 phương thức thanh toán online:
1. **VNPAY** - Thanh toán qua ví VNPAY
2. **MBBank** - Thanh toán qua ví MBBank

---

## 🔍 1. Kiểm tra Cấu hình

### VNPAY

Kiểm tra file `.env`:

```bash
VNPAY_TMN_CODE=MUAP6QM1
VNPAY_HASH_SECRET=QPMYQVFJSIV5UIIC5RF1U8HKPDLHI21D
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_API_URL=https://sandbox.vnpayment.vn/merchant_webapi/api/transaction
VNPAY_RETURN_URL=http://192.168.1.61:8000/api/client/payment/vnpay/callback
```

**Lệnh kiểm tra:**
```bash
Get-Content .env | Select-String -Pattern "VNPAY"
```

### MBBank

MBBank không cần cấu hình trong `.env` vì sử dụng API public:
- API URL: `https://api-mb.midstack.io.vn/api/transactions`
- Không cần API key hoặc secret

---

## 🧪 2. Test API Endpoints

### Test VNPAY

**Endpoint:** `POST /api/client/payment/vnpay`

**Request:**
```json
{
  "order_id": 25,
  "amount": 2500000,
  "order_info": "Thanh toán đơn hàng #DH9CM2WN1E"
}
```

**Response thành công:**
```json
{
  "status": true,
  "code": "00",
  "message": "Tạo link thanh toán VNPAY thành công",
  "data": {
    "payment_url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...",
    "txn_ref": "ORDER_25_1764016881"
  }
}
```

**Test bằng cURL:**
```bash
curl -X POST http://192.168.1.61:8000/api/client/payment/vnpay \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "order_id": 25,
    "amount": 2500000,
    "order_info": "Test payment"
  }'
```

### Test MBBank

**Endpoint:** `POST /api/client/payment/mbbank`

**Request:**
```json
{
  "order_id": 25,
  "amount": 2500000,
  "order_info": "Thanh toán đơn hàng #DH9CM2WN1E",
  "customer_name": "Nguyễn Văn A",
  "customer_email": "test@example.com",
  "customer_phone": "0901234567"
}
```

**Response thành công:**
```json
{
  "status": true,
  "message": "Tạo giao dịch MBBank thành công",
  "data": {
    "payment_url": "...",
    "qr_code": "...",
    "transaction_id": "..."
  }
}
```

**Test bằng cURL:**
```bash
curl -X POST http://192.168.1.61:8000/api/client/payment/mbbank \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "order_id": 25,
    "amount": 2500000,
    "order_info": "Test payment",
    "customer_name": "Nguyễn Văn A"
  }'
```

### Test MBBank API (Debug)

**Endpoint:** `GET /api/client/payment/mbbank/test`

Test kết nối đến MBBank API:

```bash
curl http://192.168.1.61:8000/api/client/payment/mbbank/test
```

---

## 📊 3. Kiểm tra Log

### Xem log VNPAY

```bash
# Xem tất cả log VNPAY
tail -f storage/logs/laravel.log | grep -i vnpay

# Hoặc trong PowerShell
Get-Content storage/logs/laravel.log | Select-String -Pattern "VNPAY"
```

**Các log quan trọng:**
- `VNPayService Initialized` - Kiểm tra cấu hình
- `VNPay Payment URL Created` - Kiểm tra tạo payment URL
- `VNPAY Callback - Route Hit` - Callback từ VNPAY
- `VNPAY Callback - Payment Success` - Thanh toán thành công

### Xem log MBBank

```bash
# Xem tất cả log MBBank
tail -f storage/logs/laravel.log | grep -i mbbank

# Hoặc trong PowerShell
Get-Content storage/logs/laravel.log | Select-String -Pattern "MBBank"
```

**Các log quan trọng:**
- `MBBank Payment Request` - Request gửi đến MBBank
- `MBBank Payment Response` - Response từ MBBank
- `MBBank Payment Failed` - Lỗi thanh toán

---

## ✅ 4. Checklist Kiểm tra

### VNPAY

- [ ] `VNPAY_TMN_CODE` đã được cấu hình trong `.env`
- [ ] `VNPAY_HASH_SECRET` đã được cấu hình trong `.env`
- [ ] `VNPAY_RETURN_URL` đã được cấu hình và accessible
- [ ] Terminal ID đã được đăng ký trong VNPAY Sandbox
- [ ] Return URL đã được đăng ký trong VNPAY Sandbox
- [ ] Server đã được restart sau khi sửa `.env`
- [ ] Log hiển thị `VNPayService Initialized` với đầy đủ thông tin
- [ ] Payment URL được tạo thành công
- [ ] Callback được gọi khi thanh toán thành công

### MBBank

- [ ] API endpoint `https://api-mb.midstack.io.vn/api/transactions` accessible
- [ ] Test endpoint `/api/client/payment/mbbank/test` trả về response
- [ ] Payment request được gửi thành công
- [ ] Response từ MBBank có `payment_url` hoặc `qr_code`
- [ ] Log hiển thị `MBBank Payment Response` với status 200

---

## 🐛 5. Xử lý Lỗi

### VNPAY - Lỗi Code 99

**Nguyên nhân:**
- HashSecret không đúng
- Terminal chưa được kích hoạt
- Return URL không đúng

**Giải pháp:**
Xem file `DEBUG_VNPAY_ERROR_99.md`

### VNPAY - Lỗi Code 97

**Nguyên nhân:** Chữ ký không hợp lệ

**Giải pháp:**
- Kiểm tra lại `VNPAY_HASH_SECRET` trong `.env`
- Đảm bảo không có khoảng trắng thừa
- Restart server

### MBBank - Connection Error

**Nguyên nhân:** Không thể kết nối đến MBBank API

**Giải pháp:**
- Kiểm tra kết nối internet
- Kiểm tra firewall
- Test endpoint: `GET /api/client/payment/mbbank/test`

### MBBank - Invalid Response

**Nguyên nhân:** Response từ MBBank không hợp lệ

**Giải pháp:**
- Kiểm tra log `MBBank Payment Response`
- Kiểm tra format của request payload
- Liên hệ MBBank support nếu cần

---

## 🔄 6. Test Flow Hoàn chỉnh

### Test VNPAY Flow

1. **Tạo đơn hàng:**
   ```bash
   POST /api/client/don-hang
   {
     "product_id": 1,
     "quantity": 1,
     "payment_method": "vnpay",
     "buyer_name": "Test User",
     "buyer_phone": "0901234567"
   }
   ```

2. **Tạo payment URL:**
   ```bash
   POST /api/client/payment/vnpay
   {
     "order_id": 25,
     "amount": 2500000
   }
   ```

3. **Mở payment URL trong browser**

4. **Thanh toán bằng tài khoản test VNPAY**

5. **Kiểm tra callback:**
   - Xem log: `VNPAY Callback - Payment Success`
   - Kiểm tra đơn hàng đã được cập nhật `payment_status = 'paid'`

### Test MBBank Flow

1. **Tạo đơn hàng:**
   ```bash
   POST /api/client/don-hang
   {
     "product_id": 1,
     "quantity": 1,
     "payment_method": "mbbank",
     "buyer_name": "Test User",
     "buyer_phone": "0901234567"
   }
   ```

2. **Tạo payment:**
   ```bash
   POST /api/client/payment/mbbank
   {
     "order_id": 25,
     "amount": 2500000,
     "customer_name": "Test User"
   }
   ```

3. **Nhận QR code hoặc payment URL**

4. **Quét QR hoặc mở payment URL**

5. **Kiểm tra đơn hàng đã được cập nhật**

---

## 📝 7. Log Mẫu

### VNPAY - Log thành công

```
[2025-11-24 20:41:21] local.INFO: VNPayService Initialized {
  "url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html",
  "tmn_code": "MUAP6QM1",
  "return_url": "http://192.168.1.61:8000/api/client/payment/vnpay/callback",
  "has_secret": true
}

[2025-11-24 20:41:21] local.INFO: VNPay Payment URL Created {
  "tmn_code": "MUAP6QM1",
  "amount": 250000000,
  "txn_ref": "ORDER_25_1764016881",
  "has_hash": true
}
```

### MBBank - Log thành công

```
[2025-11-24 20:41:21] local.INFO: MBBank Payment Request {
  "url": "https://api-mb.midstack.io.vn/api/transactions",
  "payload": {
    "amount": 2500000,
    "description": "Thanh toán đơn hàng #DH9CM2WN1E",
    "order_id": "ORDER_25"
  }
}

[2025-11-24 20:41:21] local.INFO: MBBank Payment Response {
  "status": 200,
  "body": "{\"payment_url\":\"...\",\"qr_code\":\"...\"}"
}
```

---

## 🔗 8. Links Hữu ích

- **VNPAY Sandbox:** https://sandbox.vnpayment.vn/
- **VNPAY Documentation:** https://sandbox.vnpayment.vn/apis/docs/
- **MBBank API:** https://api-mb.midstack.io.vn/api/transactions

---

## 📞 9. Liên hệ Hỗ trợ

- **VNPAY:** hotrovnpay@vnpay.vn
- **MBBank:** Liên hệ qua website chính thức

---

## ✅ Kết luận

Sau khi kiểm tra tất cả các bước trên, nếu mọi thứ đều OK, hệ thống thanh toán đã sẵn sàng sử dụng!

**Lưu ý:** Luôn test trên môi trường Sandbox trước khi chuyển sang Production.

