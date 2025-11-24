# ⚡ SỬA NHANH: KHÔNG UPLOAD ẢNH QUA LINK CHIA SẺ

> Hướng dẫn nhanh để fix lỗi upload ảnh và đăng bán khi chia sẻ link

---

## 🚀 GIẢI PHÁP NHANH (3 bước)

### Bước 1: Tạo file `.env` trong Frontend

```bash
cd FE_Second-hand-Goods-Trading-Platform
```

**Cách 1: Copy từ .env.example (Khuyên dùng)**
```bash
copy .env.example .env    # Windows
# hoặc
cp .env.example .env      # Linux/Mac
```

**Cách 2: Tạo thủ công file `.env`**
```env
VITE_API_BASE_URL=/api/client
```

**Lưu ý**: File `.env.example` đã có sẵn với cấu hình đúng!

### Bước 2: Cập nhật CORS trong Backend

File `config/cors.php` đã được cập nhật với `supports_credentials: true`

Clear config cache:
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan config:clear
```

### Bước 3: Restart services

```bash
# Terminal 1: Backend
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Frontend (DỪNG và CHẠY LẠI)
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```

---

## ✅ XONG!

Bây giờ truy cập `http://[IP]:5173` từ máy khác và thử:
- ✅ Upload ảnh
- ✅ Đăng bán sản phẩm

---

## 🔍 NẾU VẪN LỖI

1. **Mở Browser Console (F12)**
2. **Xem lỗi cụ thể**:
   - CORS error → Kiểm tra `config/cors.php`
   - Network error → Kiểm tra API URL
   - 401/403 → Kiểm tra token authentication

3. **Kiểm tra Network tab**:
   - Request URL phải là `/api/client/san-pham` (relative)
   - Method: POST
   - Headers có `Authorization: Bearer ...`

---

Xem chi tiết tại: [FIX_UPLOAD_ANH_QUA_LINK_CHIA_SE.md](./FIX_UPLOAD_ANH_QUA_LINK_CHIA_SE.md)

