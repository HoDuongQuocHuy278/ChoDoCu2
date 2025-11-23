# 🚀 LỆNH CHẠY NHANH

> ⚠️ **Lưu ý**: Đây là lệnh chạy nhanh. Để xem hướng dẫn chi tiết từ đầu, xem file [HUONG_DAN_CAI_DAT_VA_CHAY.md](./HUONG_DAN_CAI_DAT_VA_CHAY.md)

---

## 📋 CÀI ĐẶT LẦN ĐẦU

### Backend (Laravel)
```bash
cd BE_Second-hand-Goods-Trading-Platform
composer install
copy .env.example .env
php artisan key:generate
# Cấu hình database trong file .env
php artisan migrate:fresh --seed
php artisan storage:link
```

### Frontend (Vue.js)
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm install
npm run dev
```

### Chatbox (Python)
```bash
cd chatbox
python -m venv venv
venv\Scripts\activate    # Windows
# hoặc: source venv/bin/activate  # Linux/Mac
pip install -r requirements.txt
python -c "import nltk; nltk.download('punkt')"
python train.py
```

---

## 🏃 CHẠY DỰ ÁN

### Chạy cho bản thân (localhost)

#### Terminal 1: Backend
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve
```
**URL**: http://127.0.0.1:8000

#### Terminal 2: Frontend
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```
**URL**: http://localhost:5173

#### Terminal 3: Chatbox
```bash
cd chatbox
venv\Scripts\activate    # Windows (nếu dùng venv)
python app.py
```
**URL**: http://127.0.0.1:5000

---

### 🌐 Chia sẻ cho người cùng mạng

#### Bước 1: Lấy IP của bạn
```bash
# Windows
ipconfig
# Tìm "IPv4 Address" (ví dụ: 192.168.1.100)

# Linux/Mac
hostname -I
```

#### Bước 2: Chạy với host 0.0.0.0

**Terminal 1: Backend**
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2: Frontend**
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
# Đã cấu hình sẵn trong vite.config.js
```

**Terminal 3: Chatbox**
```bash
cd chatbox
venv\Scripts\activate    # Windows (nếu dùng venv)
python app.py
# Đã cấu hình sẵn host=0.0.0.0
```

#### Bước 3: Truy cập từ máy khác
- Frontend: `http://[IP_CUA_BAN]:5173`
- Backend: `http://[IP_CUA_BAN]:8000`
- Chatbox: `http://[IP_CUA_BAN]:5000`

**Ví dụ**: Nếu IP của bạn là `192.168.1.100`:
- Frontend: `http://192.168.1.100:5173`
- Backend: `http://192.168.1.100:8000`
- Chatbox: `http://192.168.1.100:5000`

**Lưu ý**: Có thể cần mở Firewall cho các port 8000, 5173, 5000

---

## 🔄 RESET DATABASE

Nếu muốn reset database và load lại dữ liệu mẫu:
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan migrate:fresh --seed
```

---

## 📚 XEM HƯỚNG DẪN CHI TIẾT

Xem file [HUONG_DAN_CAI_DAT_VA_CHAY.md](./HUONG_DAN_CAI_DAT_VA_CHAY.md) để biết:
- ✅ Yêu cầu hệ thống
- ✅ Cách cài đặt từng công cụ
- ✅ Cấu hình chi tiết
- ✅ Xử lý lỗi thường gặp
- ✅ Và nhiều hơn nữa...

