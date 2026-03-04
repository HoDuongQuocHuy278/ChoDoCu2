# Hướng Dẫn Sử Dụng Auto Check Payment

## 📌 Mô tả
Hàm `autoCheckPayment` tự động kiểm tra lịch sử giao dịch từ MBBank API và cập nhật trạng thái thanh toán cho các đơn hàng phù hợp.

## 🔧 Cấu hình

### 1. Thêm vào file `.env`:
```env
MBBANK_USERNAME=your_username
MBBANK_PASSWORD=your_password  
MBBANK_ACCOUNT_NUMBER=your_account_number
MBBANK_API_URL=https://api-mb.midstack.io.vn/api/transactions
```

### 2. Cài đặt Guzzle (nếu chưa có):
```bash
composer require guzzlehttp/guzzle
```

## 🚀 Cách sử dụng

### Phương pháp 1: Gọi API thủ công
```bash
POST /api/client/payment/auto-check
```

**Request Body (optional):**
```json
{
  "day_begin": "30/11/2025",
  "day_end": "30/11/2025"
}
```

**Response Success:**
```json
{
  "status": true,
  "message": "Kiểm tra thanh toán tự động hoàn tất",
  "data": {
    "total_transactions": 5,
    "updated_orders": [
      {
        "order_id": 123,
        "ma_don_hang": "DH123456",
        "amount": 500000,
        "transaction_id": "FT12345678"
      }
    ],
    "updated_count": 1,
    "skipped_orders": [],
    "skipped_count": 0,
    "date_range": {
      "from": "30/11/2025",
      "to": "30/11/2025"
    }
  }
}
```

### Phương pháp 2: Cronjob tự động (khuyến nghị)

**Thêm vào crontab (Linux/Mac):**
```bash
# Chạy mỗi 5 phút
*/5 * * * * curl -X POST http://your-domain.com/api/client/payment/auto-check

# Hoặc dùng Laravel Schedule
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Thêm vào `app/Console/Kernel.php` (Laravel Schedule):**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $controller = new \App\Http\Controllers\DonHangController();
        $request = new \Illuminate\Http\Request();
        $controller->autoCheckPayment($request);
    })->everyFiveMinutes();
}
```

## 📋 Format mô tả chuyển khoản

Khi chuyển khoản, khách hàng cần ghi nội dung theo format:
- **`CHODC{id}`** - Ví dụ: `CHODC123` cho đơn hàng ID 123
- **`DH{id}`** - Ví dụ: `DH456` cho đơn hàng ID 456

Hệ thống sẽ tự động:
1. Quét lịch sử giao dịch
2. Tìm đơn hàng theo ID trong mô tả
3. Kiểm tra số tiền khớp
4. Cập nhật `payment_status = 'paid'`
5. Lưu thông tin giao dịch vào `payment_payload`

## ⚠️ Lưu ý

1. **Bảo mật**: Không commit file `.env` chứa thông tin thật lên Git
2. **API Rate Limit**: MBBank API có thể giới hạn số lần gọi, nên set cronjob hợp lý
3. **Số tiền phải khớp**: Hệ thống chỉ cập nhật khi số tiền giao dịch = tổng tiền đơn hàng
4. **Chỉ check đơn online**: Chỉ kiểm tra đơn có `payment_method` là vnpay, mbbank, momo
5. **Không check đơn đã thanh toán**: Chỉ check đơn có `payment_status != 'paid'`

## 🔍 Debug

Check log tại `storage/logs/laravel.log`:
```bash
tail -f storage/logs/laravel.log | grep "MBBank"
```

## 📧 TODO

- [ ] Gửi email thông báo thanh toán thành công cho khách
- [ ] Gửi thông báo cho người bán
- [ ] Tích hợp webhook từ MBBank (nếu có)
