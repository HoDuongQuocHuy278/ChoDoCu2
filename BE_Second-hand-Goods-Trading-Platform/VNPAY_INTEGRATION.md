# Hướng dẫn tích hợp VNPay với Backend Laravel

## Tổng quan

Hệ thống đã được tích hợp VNPay để xử lý thanh toán trực tuyến. Tất cả logic VNPay được tách riêng vào `VNPayService` để dễ bảo trì và tái sử dụng.

## Cấu trúc tích hợp

### 1. Service Class
- **File**: `app/Services/VNPayService.php`
- **Chức năng**: Xử lý tất cả logic liên quan đến VNPay
  - Tạo URL thanh toán
  - Xác thực chữ ký
  - Parse dữ liệu callback
  - Gọi API VNPay (refund, query)

### 2. Controller
- **File**: `app/Http/Controllers/ThanhToanController.php`
- **Methods**:
  - `vnpay_payment()`: Tạo link thanh toán VNPay
  - `vnpayCallback()`: Xử lý callback từ VNPay sau thanh toán
  - `vnpayRefund()`: Hoàn tiền giao dịch
  - `vnpayQuery()`: Tra cứu giao dịch

### 3. Config
- **File**: `config/vnpay.php`
- Chứa cấu hình VNPay (URL, version, currency, etc.)

### 4. Routes
- **File**: `routes/api.php`
- Routes:
  - `POST /api/client/payment/vnpay`: Tạo thanh toán
  - `GET /api/client/payment/vnpay/callback`: Callback từ VNPay
  - `POST /api/client/payment/vnpay/refund`: Hoàn tiền (yêu cầu auth)
  - `POST /api/client/payment/vnpay/query`: Tra cứu giao dịch (yêu cầu auth)

### 5. View
- **File**: `resources/views/vnpay_callback.blade.php`
- Hiển thị kết quả thanh toán và redirect về frontend

## Cấu hình

### 1. Thêm vào file `.env`

```env
# VNPay Configuration
VNPAY_TMN_CODE=YOUR_TERMINAL_CODE
VNPAY_HASH_SECRET=YOUR_SECRET_KEY
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost:8000/api/client/payment/vnpay/callback
VNPAY_API_URL=https://sandbox.vnpayment.vn/merchant_webapi/api/transaction

# Frontend URL (để redirect sau khi thanh toán)
FRONTEND_URL=http://localhost:5173
```

### 2. Lấy thông tin test từ VNPay Sandbox

1. Truy cập: https://sandbox.vnpayment.vn/
2. Đăng ký/đăng nhập tài khoản test
3. Lấy thông tin:
   - **Terminal Code (TmnCode)**: Mã định danh merchant
   - **Hash Secret**: Secret key để tạo chữ ký

## Cách sử dụng

### 1. Tạo thanh toán từ Frontend

```javascript
// Gọi API để tạo link thanh toán
const response = await fetch('http://localhost:8000/api/client/payment/vnpay', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_TOKEN' // Nếu cần
    },
    body: JSON.stringify({
        order_id: 123, // ID đơn hàng (optional)
        amount: 100000, // Số tiền (VNĐ)
        order_info: 'Thanh toán đơn hàng #123', // Mô tả (optional)
        bank_code: '', // Mã ngân hàng (optional, để trống để chọn tự do)
        locale: 'vn' // Ngôn ngữ: 'vn' hoặc 'en' (optional)
    })
});

const data = await response.json();

if (data.status) {
    // Redirect đến VNPay
    window.location.href = data.data.payment_url;
}
```

### 2. Xử lý callback

Sau khi thanh toán, VNPay sẽ redirect về:
- Backend: `/api/client/payment/vnpay/callback`
- Backend sẽ xử lý và redirect về Frontend: `/payment/callback?RspCode=00&Message=...`

Frontend cần xử lý tại route `/payment/callback`:

```javascript
// Trong component xử lý callback
const params = new URLSearchParams(window.location.search);
const rspCode = params.get('RspCode');
const message = params.get('Message');
const orderCode = params.get('order_code');

if (rspCode === '00') {
    // Thanh toán thành công
    console.log('Thanh toán thành công!', { orderCode, message });
    // Redirect đến trang đơn hàng hoặc trang thành công
} else {
    // Thanh toán thất bại
    console.error('Thanh toán thất bại:', message);
    // Hiển thị thông báo lỗi
}
```

## Luồng thanh toán

1. **User chọn thanh toán VNPay** → Frontend gọi API `POST /api/client/payment/vnpay`
2. **Backend tạo payment URL** → Trả về `payment_url` và `txn_ref`
3. **Frontend redirect** → User được chuyển đến VNPay
4. **User thanh toán** → Trên cổng VNPay
5. **VNPay callback** → VNPay gọi `GET /api/client/payment/vnpay/callback`
6. **Backend xử lý**:
   - Xác thực chữ ký
   - Cập nhật trạng thái đơn hàng
   - Tạo thông báo
   - Redirect về Frontend
7. **Frontend hiển thị kết quả** → Tại `/payment/callback`

## Cập nhật trạng thái đơn hàng

Khi thanh toán thành công, Backend sẽ tự động:
- Cập nhật `payment_status` = `'paid'`
- Cập nhật `status` = `'processing'`
- Lưu thông tin thanh toán vào `payment_payload`
- Tạo thông báo cho buyer và seller

## Hoàn tiền (Refund)

### API Endpoint
```
POST /api/client/payment/vnpay/refund
Authorization: Bearer {token}
```

### Request Body
```json
{
    "txn_ref": "ORDER_123_1234567890",
    "transaction_date": "20250101120000",
    "amount": 100000,
    "transaction_type": "02",
    "transaction_no": "12345678",
    "order_info": "Hoan tien don hang #123",
    "create_by": "admin"
}
```

### Parameters
- `txn_ref` (required): Mã tham chiếu giao dịch cần hoàn (format: ORDER_{id}_{timestamp})
- `transaction_date` (required): Thời gian giao dịch thanh toán (format: yyyyMMddHHmmss)
- `amount` (required): Số tiền hoàn (VNĐ, tối thiểu 1000)
- `transaction_type` (optional): 
  - `"02"`: Hoàn tiền toàn phần (mặc định)
  - `"03"`: Hoàn tiền một phần
- `transaction_no` (optional): Mã giao dịch VNPay (để "0" nếu không có)
- `order_info` (optional): Mô tả
- `create_by` (optional): Người tạo hoàn tiền

### Response
```json
{
    "status": true,
    "code": "00",
    "message": "Hoàn tiền thành công",
    "data": {
        "vnp_ResponseCode": "00",
        "vnp_Message": "Success",
        ...
    }
}
```

### Ví dụ sử dụng
```javascript
const response = await fetch('http://localhost:8000/api/client/payment/vnpay/refund', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_TOKEN'
    },
    body: JSON.stringify({
        txn_ref: 'ORDER_123_1234567890',
        transaction_date: '20250101120000',
        amount: 100000,
        transaction_type: '02'
    })
});

const data = await response.json();
```

## Tra cứu giao dịch (Query Transaction)

### API Endpoint
```
POST /api/client/payment/vnpay/query
Authorization: Bearer {token}
```

### Request Body
```json
{
    "txn_ref": "ORDER_123_1234567890",
    "transaction_date": "20250101120000",
    "order_info": "Query transaction"
}
```

### Parameters
- `txn_ref` (required): Mã tham chiếu giao dịch
- `transaction_date` (required): Thời gian giao dịch (format: yyyyMMddHHmmss)
- `order_info` (optional): Mô tả

### Response
```json
{
    "status": true,
    "code": "00",
    "message": "Tra cứu thành công",
    "data": {
        "vnp_ResponseCode": "00",
        "vnp_Message": "Success",
        "vnp_TxnRef": "ORDER_123_1234567890",
        "vnp_TransactionNo": "12345678",
        "vnp_Amount": "10000000",
        "vnp_TransactionStatus": "00",
        ...
    }
}
```

### Ví dụ sử dụng
```javascript
const response = await fetch('http://localhost:8000/api/client/payment/vnpay/query', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_TOKEN'
    },
    body: JSON.stringify({
        txn_ref: 'ORDER_123_1234567890',
        transaction_date: '20250101120000'
    })
});

const data = await response.json();
```

## Mã lỗi VNPay

- **00**: Giao dịch thành công
- **07**: Giao dịch bị nghi ngờ
- **09**: Thẻ/Tài khoản chưa đăng ký dịch vụ
- **10**: Xác thực thông tin thẻ/tài khoản không đúng
- **11**: Đã hết hạn chờ thanh toán
- **12**: Thẻ/Tài khoản bị khóa
- **51**: Tài khoản không đủ số dư
- **65**: Tài khoản vượt quá hạn mức giao dịch
- **71**: Terminal (website) chưa được phê duyệt → Kiểm tra TmnCode và HashSecret
- **75**: Ngân hàng đang bảo trì
- **97**: Chữ ký không hợp lệ → Kiểm tra HashSecret
- **99**: Lỗi không xác định

## Testing

### 1. Test với Sandbox

1. Đảm bảo đã cấu hình đúng trong `.env`
2. Tạo đơn hàng từ Frontend
3. Chọn thanh toán VNPay
4. Sử dụng tài khoản test từ VNPay Sandbox để thanh toán

### 2. Kiểm tra logs

Backend sẽ log tất cả các bước:
- `storage/logs/laravel.log`

Tìm các log với tag:
- `VNPay Payment URL Created`
- `VNPAY Callback - Route Hit`
- `VNPAY Callback - Processing`
- `VNPAY Callback - Payment Success/Failed`

## Troubleshooting

### Lỗi Code 71: Terminal not approved
- **Nguyên nhân**: Chưa điền hoặc điền sai `VNPAY_TMN_CODE` và `VNPAY_HASH_SECRET`
- **Giải pháp**: Lấy thông tin từ VNPay Sandbox và cập nhật `.env`

### Lỗi Code 97: Invalid signature
- **Nguyên nhân**: `VNPAY_HASH_SECRET` không đúng
- **Giải pháp**: Kiểm tra lại HashSecret trong `.env`

### Callback không được gọi
- **Nguyên nhân**: `VNPAY_RETURN_URL` không đúng hoặc không accessible từ internet
- **Giải pháp**: 
  - Kiểm tra URL trong `.env`
  - Nếu test local, có thể dùng ngrok để expose localhost

### Frontend không nhận được redirect
- **Nguyên nhân**: `FRONTEND_URL` không đúng
- **Giải pháp**: Cập nhật `FRONTEND_URL` trong `.env`

## Lưu ý bảo mật

1. ✅ **Luôn kiểm tra chữ ký** trong callback
2. ✅ **Validate số tiền** trước khi cập nhật database
3. ✅ **Không commit file `.env`** với thông tin thật
4. ✅ **Sử dụng HTTPS** trong production
5. ✅ **Kiểm tra IPN** (nếu có) để đảm bảo giao dịch thật

## Production

Khi chuyển sang production:

1. Thay đổi URL trong `.env`:
   ```env
   VNPAY_URL=https://www.vnpayment.vn/paymentv2/vpcpay.html
   VNPAY_API_URL=https://www.vnpayment.vn/merchant_webapi/api/transaction
   ```

2. Lấy thông tin production từ VNPay:
   - Terminal Code (production)
   - Hash Secret (production)

3. Cập nhật `VNPAY_RETURN_URL` với domain thật:
   ```env
   VNPAY_RETURN_URL=https://yourdomain.com/api/client/payment/vnpay/callback
   ```

4. Đảm bảo Frontend URL đúng:
   ```env
   FRONTEND_URL=https://yourdomain.com
   ```


