# 🔧 SỬA LỖI: KHÔNG UPLOAD ẢNH VÀ ĐĂNG BÁN QUA LINK CHIA SẺ

> Hướng dẫn sửa lỗi khi truy cập qua link chia sẻ (ngrok/LAN) không upload được ảnh và không đăng bán được

---

## 🐛 VẤN ĐỀ

Khi chia sẻ link qua mạng (ngrok hoặc LAN), người dùng:
- ✅ Đăng nhập được
- ❌ Không upload được ảnh
- ❌ Không đăng bán được sản phẩm

---

## 🔍 NGUYÊN NHÂN

1. **API URL không đúng**: Frontend đang dùng absolute URL `http://127.0.0.1:8000` thay vì relative URL
2. **CORS chưa cấu hình**: Backend chưa cho phép CORS từ domain khác
3. **File upload qua proxy**: FormData có thể không được proxy đúng cách

---

## ✅ GIẢI PHÁP

### Bước 1: Cấu hình Frontend sử dụng Relative URL

#### Tạo/Cập nhật file `.env` trong Frontend:

```bash
cd FE_Second-hand-Goods-Trading-Platform
```

Tạo file `.env`:
```env
# Sử dụng relative URL để hoạt động với proxy
VITE_API_BASE_URL=/api/client
```

**Lưu ý**: 
- Relative URL (`/api/client`) sẽ tự động được proxy qua Vite
- Không cần thay đổi code, chỉ cần set environment variable

#### Hoặc nếu muốn dùng absolute URL cho LAN:

```env
# Thay [IP_CUA_BAN] bằng IP thực tế của bạn
VITE_API_BASE_URL=http://192.168.1.100:8000/api/client
```

**Sau khi cập nhật `.env`, cần restart Frontend server:**
```bash
# Dừng server (Ctrl + C)
# Chạy lại
npm run dev
```

---

### Bước 2: Cấu hình CORS trong Backend

#### Tạo file cấu hình CORS (nếu chưa có):

Laravel 11+ không có file `config/cors.php` mặc định. Tạo file mới:

```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan config:publish cors
```

Hoặc tạo thủ công file `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // Cho phép tất cả origins (development)
    // Hoặc chỉ định cụ thể:
    // 'allowed_origins' => [
    //     'http://localhost:5173',
    //     'http://127.0.0.1:5173',
    //     'https://your-ngrok-url.ngrok-free.app',
    //     'http://192.168.1.100:5173', // IP của bạn
    // ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Quan trọng cho file upload
];
```

#### Cập nhật `.env` trong Backend:

```env
# Thêm vào file .env
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,192.168.1.100:5173,your-ngrok-url.ngrok-free.app

# Nếu dùng ngrok, thêm domain ngrok vào
# SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,your-ngrok-url.ngrok-free.app
```

**Lưu ý**: Thay `192.168.1.100` và `your-ngrok-url.ngrok-free.app` bằng giá trị thực tế.

---

### Bước 3: Đảm bảo Vite Proxy hoạt động đúng

File `vite.config.js` đã được cấu hình sẵn với proxy. Kiểm tra lại:

```javascript
// FE_Second-hand-Goods-Trading-Platform/vite.config.js
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
```

**Lưu ý**: 
- `changeOrigin: true` - Quan trọng cho CORS
- `secure: false` - Cho phép HTTP (không chỉ HTTPS)

---

### Bước 4: Kiểm tra File Upload Size Limit

#### Backend - Cập nhật `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

#### Hoặc trong Laravel `.env`:

```env
# Nếu dùng nginx
NGINX_CLIENT_MAX_BODY_SIZE=10M
```

---

### Bước 5: Test lại

1. **Restart tất cả services**:
   ```bash
   # Terminal 1: Backend
   cd BE_Second-hand-Goods-Trading-Platform
   php artisan serve --host=0.0.0.0 --port=8000
   
   # Terminal 2: Frontend
   cd FE_Second-hand-Goods-Trading-Platform
   npm run dev
   ```

2. **Truy cập từ máy khác**:
   - URL: `http://[IP_CUA_BAN]:5173`
   - Đăng nhập
   - Thử upload ảnh và đăng bán

3. **Kiểm tra Console (F12)**:
   - Xem có lỗi CORS không
   - Xem network requests có thành công không

---

## 🔧 XỬ LÝ LỖI CỤ THỂ

### Lỗi 1: "CORS policy: No 'Access-Control-Allow-Origin'"

**Nguyên nhân**: Backend chưa cho phép CORS từ origin của Frontend.

**Giải pháp**:
1. Tạo file `config/cors.php` (xem Bước 2)
2. Đảm bảo `allowed_origins` bao gồm domain của Frontend
3. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

### Lỗi 2: "Network Error" hoặc "Failed to fetch"

**Nguyên nhân**: API URL không đúng hoặc không thể kết nối.

**Giải pháp**:
1. Kiểm tra `.env` trong Frontend có `VITE_API_BASE_URL=/api/client`
2. Kiểm tra Vite proxy đang chạy
3. Kiểm tra Backend đang chạy với `--host=0.0.0.0`

### Lỗi 3: "413 Request Entity Too Large"

**Nguyên nhân**: File quá lớn, vượt quá limit.

**Giải pháp**:
1. Tăng `upload_max_filesize` trong `php.ini`
2. Tăng `post_max_size` trong `php.ini`
3. Restart PHP/Apache

### Lỗi 4: "401 Unauthorized" khi upload

**Nguyên nhân**: Token không được gửi đúng hoặc đã hết hạn.

**Giải pháp**:
1. Kiểm tra token trong localStorage: `localStorage.getItem('key_client')`
2. Kiểm tra Authorization header có được gửi không
3. Refresh token nếu cần

### Lỗi 5: FormData không được gửi qua proxy

**Nguyên nhân**: Vite proxy có thể không handle FormData đúng cách.

**Giải pháp**:
1. Đảm bảo `Content-Type: multipart/form-data` không được set manually
2. Axios sẽ tự động set Content-Type cho FormData
3. Kiểm tra Vite proxy config có `changeOrigin: true`

---

## 📝 CẬP NHẬT CODE (Nếu cần)

### Frontend - Đảm bảo API URL đúng:

File: `FE_Second-hand-Goods-Trading-Platform/src/components/NguoiDangBan/Sell/index.vue`

Đảm bảo dòng 225:
```javascript
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/client'
```

**Lưu ý**: Default value là `/api/client` (relative URL) để hoạt động với proxy.

### Backend - Thêm CORS middleware (nếu cần):

File: `BE_Second-hand-Goods-Trading-Platform/bootstrap/app.php`

Đảm bảo CORS middleware được enable (Laravel tự động có).

---

## ✅ CHECKLIST

Sau khi sửa, kiểm tra:

- [ ] File `.env` trong Frontend có `VITE_API_BASE_URL=/api/client`
- [ ] File `config/cors.php` trong Backend đã được tạo
- [ ] File `.env` trong Backend có `SANCTUM_STATEFUL_DOMAINS` đúng
- [ ] Vite proxy config đúng trong `vite.config.js`
- [ ] Backend chạy với `--host=0.0.0.0`
- [ ] Frontend đã restart sau khi đổi `.env`
- [ ] Backend đã clear config cache
- [ ] Test upload ảnh thành công
- [ ] Test đăng bán sản phẩm thành công

---

## 🧪 TEST TỪNG BƯỚC

### Test 1: Kiểm tra API có hoạt động không

Từ máy khác, mở browser console và chạy:
```javascript
fetch('/api/client/san-pham')
  .then(r => r.json())
  .then(console.log)
  .catch(console.error)
```

Nếu thấy data, API proxy hoạt động tốt! ✅

### Test 2: Kiểm tra upload ảnh

1. Mở trang đăng bán: `http://[IP]:5173/dang-ban`
2. Click "Thêm ảnh"
3. Chọn file ảnh
4. Xem console có lỗi không

### Test 3: Test đăng bán

1. Điền form đầy đủ
2. Thêm ít nhất 1 ảnh
3. Click "Đăng bán"
4. Xem network tab trong DevTools:
   - Request URL: `/api/client/san-pham`
   - Method: POST
   - Status: 200 hoặc 201
   - Request payload: FormData có `images[]`

---

## 🎯 GIẢI PHÁP NHANH (Tóm tắt)

### Cho LAN (Mạng nội bộ):

1. **Frontend `.env`**:
   ```env
   VITE_API_BASE_URL=/api/client
   ```

2. **Backend `.env`**:
   ```env
   SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,192.168.1.100:5173
   ```

3. **Backend `config/cors.php`**:
   ```php
   'allowed_origins' => ['*'],
   'supports_credentials' => true,
   ```

4. **Restart services**

### Cho ngrok (Internet):

1. **Frontend `.env`**:
   ```env
   VITE_API_BASE_URL=/api/client
   ```

2. **Backend `.env`**:
   ```env
   SANCTUM_STATEFUL_DOMAINS=localhost:5173,your-ngrok-url.ngrok-free.app
   ```

3. **Backend `config/cors.php`**:
   ```php
   'allowed_origins' => [
       'https://your-ngrok-url.ngrok-free.app',
   ],
   'supports_credentials' => true,
   ```

4. **Restart services**

---

## 📞 HỖ TRỢ

Nếu vẫn gặp lỗi:

1. Kiểm tra browser console (F12) - xem lỗi cụ thể
2. Kiểm tra Network tab - xem request có được gửi không
3. Kiểm tra Backend logs: `storage/logs/laravel.log`
4. Kiểm tra Vite dev server logs

---

**Cập nhật**: 2025

