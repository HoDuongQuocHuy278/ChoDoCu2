# Giải thích Luồng Thanh Toán (Checkout Flow)

File: `src/components/Checkout/index.vue`

## 📌 Tổng quan

Component Checkout hỗ trợ **2 luồng thanh toán khác nhau**:

---

## 🛒 LUỒNG 1: THANH TOÁN TỪ GIỎ HÀNG (Cart Mode)

### Khi nào được kích hoạt?
- Người dùng chọn nhiều sản phẩm trong giỏ hàng
- Nhấn nút **"Thanh toán"** từ trang Giỏ Hàng
- URL có parameter: `?cart_mode=1` hoặc `?from_cart=1`
- **Biến điều khiển**: `cartMode = true`

### Quy trình 3 bước:

#### ✅ BƯỚC 1: Kiểm tra danh sách sản phẩm
- Template: `<div v-if="cartStep === 1">`  
- Hiển thị tất cả sản phẩm đã chọn từ giỏ hàng
- Người dùng xem lại thông tin: tên, số lượng, giá từng sản phẩm
- Tổng tiền tạm tính cho tất cả sản phẩm

#### ✅ BƯỚC 2: Nhập thông tin & chọn thanh toán
- Template: `<div v-else-if="cartStep === 2">`
- **Form nhập liệu**:
  - Họ và tên (*)
  - Số điện thoại (*)
  - Email (tùy chọn)
  - Địa chỉ nhận hàng (*)
  - Ghi chú
- **Chọn phương thức thanh toán**:
  - VNPAY (Online)
  - VietQR/MBBank (QR Code)
  - COD (Tiền mặt khi nhận hàng)

#### ✅ BƯỚC 3: Xác nhận đơn hàng
- Template: `<div v-else>` (cartStep === 3)
- Hiển thị thông tin tổng quan
- Xác nhận tạo nhiều đơn hàng (1 đơn cho mỗi sản phẩm)
- Nếu thanh toán online → Hiển thị QR hoặc link thanh toán

### Đặc điểm:
- ✔️ Tạo nhiều đơn hàng (mỗi sản phẩm 1 đơn)
- ✔️ Thanh toán tổng hợp tất cả đơn
- ✔️ Có thể quay lại giỏ hàng bất cứ lúc nào

---

## 🛍️ LUỒNG 2: THANH TOÁN TRỰC TIẾP (Single Product)

### Khi nào được kích hoạt?
- Người dùng nhấn **"Mua ngay"** từ trang chi tiết sản phẩm
- Hoặc "Mua ngay" từ danh sách sản phẩm
- URL có parameter: `?product_id=xxx`
- **Biến điều khiển**: `cartMode = false`

### Quy trình 3 bước:

#### ✅ BƯỚC 1: Nhập thông tin người mua
- Template: `<div v-show="currentStep === 1">`
- Hiển thị thông tin sản phẩm đang mua
- **Form nhập liệu**:
  - Họ và tên (*)
  - Số điện thoại (*)
  - Email (tùy chọn)
  - Địa chỉ nhận hàng (*)
  - Ghi chú cho người bán
- Chọn số lượng muốn mua
- Nút: "Tiếp tục"

#### ✅ BƯỚC 2: Chọn phương thức thanh toán
- Template: `<div v-show="currentStep === 2">`
- **Chọn 1 trong 3 phương thức**:
  - 💳 VNPAY (Quét QR hoặc thẻ liên kết)
  - 📱 VietQR (Quét mã QR ngân hàng)
  - 💵 COD (Thanh toán khi nhận hàng)
- Nút: "Tiếp tục"

#### ✅ BƯỚC 3: Xác nhận đơn hàng
- Template: `<div v-show="currentStep === 3">`
- Hiển thị tổng quan đơn hàng:
  - Thông tin sản phẩm
  - Thông tin người mua
  - Phương thức thanh toán
  - Tổng tiền
- Nút: **"Xác nhận đặt hàng"**
- Sau khi xác nhận → Hiển thị màn hình thành công với QR/link thanh toán (nếu online)

### Đặc điểm:
- ✔️ Chỉ tạo 1 đơn hàng duy nhất
- ✔️ Thanh toán cho 1 sản phẩm
- ✔️ Có thể quay lại bước trước

---

## 🔄 Cách phân biệt trong code

### Biến quan trọng:
```javascript
cartMode = true   // → Thanh toán từ giỏ hàng
cartMode = false  // → Thanh toán trực tiếp
```

### Cấu trúc template:
```vue
<template>
  <div class="checkout-page">
    <!-- CART MODE -->
    <div v-if="cartMode">
      <div v-if="cartStep === 1">BƯỚC 1</div>
      <div v-else-if="cartStep === 2">BƯỚC 2</div>
      <div v-else>BƯỚC 3</div>
    </div>

    <!-- SINGLE PRODUCT MODE -->
    <template v-else>
      <div v-show="currentStep === 1">BƯỚC 1</div>
      <div v-show="currentStep === 2">BƯỚC 2</div>
      <div v-show="currentStep === 3">BƯỚC 3</div>
    </template>
  </div>
</template>
```

### API Calls khác nhau:

**Cart Mode:**
- Gọi API tạo nhiều đơn hàng: `submitCartOrder()`
- Thanh toán tổng hợp: `requestCartPayment(orderIds, totalAmount, method)`

**Single Product:**
- Gọi API tạo 1 đơn: `submitOrder()`
- Thanh toán đơn lẻ: `createPaymentNew(orderId, method)`

---

## 📁 Files liên quan

- **Frontend**: `src/components/Checkout/index.vue` (file chính)
- **Component giỏ hàng**: `src/components/GioHang/index.vue` (redirect đến checkout)
- **Component sản phẩm**: `src/components/SanPham/index.vue` (nút "Mua ngay")
- **Backend API**: `app/Http/Controllers/DonHangController.php`

---

## 💡 Lưu ý khi sửa đổi

1. ⚠️ **Luôn kiểm tra** biến `cartMode` trước khi thay đổi logic
2. ⚠️ Validation form xuất hiện ở **CẢ 2 LUỒNG** (cartStep === 2 và currentStep === 1)
3. ⚠️ Payment methods phải xử lý cho cả 2 trường hợp
4. ⚠️ Success screen khác nhau:
   - Cart mode: hiển thị danh sách nhiều đơn
   - Single: hiển thị 1 đơn duy nhất
