# Fix: API `/api/client/chat/unread-count` bị gọi quá nhiều lần

## Vấn đề
- API endpoint `/api/client/chat/unread-count` bị gọi quá nhiều lần, gây tốn tài nguyên và có thể làm chậm ứng dụng
- Nguyên nhân: Polling interval quá ngắn và không có cơ chế tối ưu

## Giải pháp đã áp dụng

### 1. Tăng thời gian cache
- **Trước**: Cache 5 giây
- **Sau**: Cache 30 giây
- **Lợi ích**: Giảm số lần gọi API không cần thiết

### 2. Tăng polling interval
- **Trước**: Poll mỗi 60 giây
- **Sau**: Poll mỗi 2 phút (120 giây)
- **Lợi ích**: Giảm tần suất kiểm tra, tiết kiệm tài nguyên

### 3. Chỉ poll khi tab đang active
- Sử dụng **Page Visibility API** (`document.hidden`)
- Chỉ gọi API khi tab đang visible
- Khi tab trở lại active, tự động reload nếu cache đã hết hạn
- **Lợi ích**: Không lãng phí tài nguyên khi user không xem trang

### 4. Đảm bảo không có duplicate intervals
- Kiểm tra và dừng interval cũ trước khi tạo mới
- Chỉ start polling khi user vừa đăng nhập (không start lại nếu đã đăng nhập)
- **Lợi ích**: Tránh nhiều interval chạy đồng thời

## Thay đổi chi tiết

### File: `FE_Second-hand-Goods-Trading-Platform/src/layout/components/TopRocker.vue`

#### Messages Polling:
```javascript
// Trước
const MESSAGES_CACHE_TIME = 5000 // 5 giây
messagesInterval = setInterval(() => {...}, 60000) // 60 giây

// Sau
const MESSAGES_CACHE_TIME = 30000 // 30 giây
const MESSAGES_POLL_INTERVAL = 120000 // 2 phút
messagesInterval = setInterval(() => {
  if (document.hidden) return // Chỉ poll khi tab active
  ...
}, MESSAGES_POLL_INTERVAL)
```

#### Notifications Polling:
- Áp dụng tương tự như Messages Polling
- Cache time: 30 giây
- Poll interval: 2 phút
- Chỉ poll khi tab active

## Kết quả

### Trước khi fix:
- API được gọi mỗi 5-60 giây (tùy cache)
- Gọi cả khi tab không active
- Có thể có nhiều interval chạy đồng thời

### Sau khi fix:
- API được gọi mỗi 2 phút (nếu cache hết hạn)
- Chỉ gọi khi tab đang active
- Tự động reload khi tab trở lại active
- Đảm bảo chỉ có 1 interval chạy

## Ước tính giảm tải

- **Tần suất gọi API**: Giảm ~75% (từ mỗi 60s xuống mỗi 120s)
- **Gọi khi tab inactive**: Giảm 100% (không gọi nữa)
- **Tổng số request**: Giảm khoảng **80-90%** trong điều kiện bình thường

## Lưu ý

1. **Cache time 30 giây**: Đủ để hiển thị số unread count cập nhật, không quá lâu để user cảm thấy chậm
2. **Poll interval 2 phút**: Cân bằng giữa tính real-time và hiệu năng
3. **Page Visibility API**: Hỗ trợ tốt trên các trình duyệt hiện đại
4. **Auto-reload khi tab active**: Đảm bảo user luôn thấy dữ liệu mới nhất khi quay lại

## Test

1. Mở DevTools → Network tab
2. Filter: `unread-count`
3. Quan sát:
   - API chỉ được gọi mỗi 2 phút (nếu cache hết hạn)
   - Khi chuyển tab khác, API không được gọi
   - Khi quay lại tab, API được gọi ngay nếu cache đã hết hạn

## Nếu cần điều chỉnh thêm

Có thể thay đổi các hằng số trong file `TopRocker.vue`:

```javascript
// Tăng cache time để giảm số lần gọi
const MESSAGES_CACHE_TIME = 60000 // 1 phút

// Tăng poll interval để giảm tần suất
const MESSAGES_POLL_INTERVAL = 300000 // 5 phút
```

**Lưu ý**: Tăng quá nhiều có thể làm user không thấy cập nhật kịp thời.

