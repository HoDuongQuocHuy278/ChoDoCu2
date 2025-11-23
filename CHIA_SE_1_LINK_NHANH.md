# ⚡ CHIA SẺ 1 LINK - HƯỚNG DẪN NHANH

> Cách nhanh nhất để chia sẻ web qua 1 đường link duy nhất

---

## 🚀 CÁCH NHANH NHẤT (Đã cấu hình sẵn)

### Bước 1: Tạo file `.env` trong Frontend

```bash
cd FE_Second-hand-Goods-Trading-Platform
copy .env.example .env    # Windows
# hoặc
cp .env.example .env      # Linux/Mac
```

File `.env` sẽ có:
```env
VITE_API_BASE_URL=/api/client
```

### Bước 2: Chạy tất cả services

**Terminal 1: Backend**
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2: Frontend**
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```

**Terminal 3: Chatbox**
```bash
cd chatbox
venv\Scripts\activate    # Windows (nếu dùng venv)
python app.py
```

### Bước 3: Lấy IP và chia sẻ

```bash
# Windows
ipconfig
# Tìm "IPv4 Address" (ví dụ: 192.168.1.100)
```

**Chia sẻ URL duy nhất**: `http://[IP_CUA_BAN]:5173`

**Ví dụ**: `http://192.168.1.100:5173`

---

## ✅ XONG!

Người khác chỉ cần truy cập 1 URL đó, tất cả tính năng sẽ hoạt động:
- ✅ Frontend
- ✅ API Backend (tự động proxy)
- ✅ Chatbox (nếu có cấu hình)

---

## 🔍 KIỂM TRA

Từ máy khác, truy cập:
- `http://[IP]:5173` - Trang chủ
- `http://[IP]:5173/api/client/san-pham` - API (qua proxy)

---

## 📚 XEM CHI TIẾT

Xem file [HUONG_DAN_CHIA_SE_1_LINK.md](./HUONG_DAN_CHIA_SE_1_LINK.md) để biết:
- Cách dùng ngrok (chia sẻ internet)
- Cấu hình Nginx/Apache
- Xử lý lỗi

