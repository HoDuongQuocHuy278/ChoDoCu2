# 🔗 HƯỚNG DẪN CHIA SẺ WEB QUA 1 ĐƯỜNG LINK DUY NHẤT

> Hướng dẫn để người khác truy cập toàn bộ web (Frontend + Backend + Chatbox) qua chỉ 1 URL

---

## 📋 MỤC LỤC

1. [Phương pháp 1: Sử dụng ngrok (Dễ nhất)](#-phương-pháp-1-sử-dụng-ngrok-dễ-nhất)
2. [Phương pháp 2: Reverse Proxy với Nginx/Apache](#-phương-pháp-2-reverse-proxy-với-nginxapache)
3. [Phương pháp 3: Cấu hình Vite Proxy](#-phương-pháp-3-cấu-hình-vite-proxy)
4. [So sánh các phương pháp](#-so-sánh-các-phương-pháp)

---

## 🚀 PHƯƠNG PHÁP 1: SỬ DỤNG NGROK (DỄ NHẤT)

### Ưu điểm:
- ✅ Dễ cài đặt và sử dụng
- ✅ Tự động tạo HTTPS
- ✅ Có thể chia sẻ ra internet (không chỉ LAN)
- ✅ Không cần cấu hình server

### Nhược điểm:
- ⚠️ URL thay đổi mỗi lần chạy (free plan)
- ⚠️ Có giới hạn băng thông (free plan)
- ⚠️ Cần internet để hoạt động

### Bước 1: Cài đặt ngrok

#### Windows:
1. Tải ngrok từ: https://ngrok.com/download
2. Giải nén file `ngrok.exe`
3. Đăng ký tài khoản miễn phí tại: https://dashboard.ngrok.com/signup
4. Lấy **Authtoken** từ dashboard
5. Chạy lệnh để xác thực:
```bash
ngrok config add-authtoken YOUR_AUTH_TOKEN
```

#### Linux/Mac:
```bash
# Linux
curl -s https://ngrok-agent.s3.amazonaws.com/ngrok.asc | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null
echo "deb https://ngrok-agent.s3.amazonaws.com buster main" | sudo tee /etc/apt/sources.list.d/ngrok.list
sudo apt update && sudo apt install ngrok

# Mac
brew install ngrok/ngrok/ngrok

# Xác thực
ngrok config add-authtoken YOUR_AUTH_TOKEN
```

### Bước 2: Cấu hình Vite Proxy

Cập nhật file `FE_Second-hand-Goods-Trading-Platform/vite.config.js`:

```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: false,
    proxy: {
      // Proxy API requests đến Backend
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      // Proxy Chatbox requests
      '/chatbox': {
        target: 'http://127.0.0.1:5000',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/chatbox/, ''),
      },
    },
  },
})
```

### Bước 3: Chạy các services

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

**Terminal 4: ngrok**
```bash
ngrok http 5173
```

### Bước 4: Lấy URL công khai

Sau khi chạy `ngrok http 5173`, bạn sẽ thấy:

```
Forwarding  https://abc123.ngrok-free.app -> http://localhost:5173
```

**URL này chính là link duy nhất để chia sẻ!** ✅

Ví dụ: `https://abc123.ngrok-free.app`

### Bước 5: Cập nhật Frontend để sử dụng proxy

Tất cả API calls trong Frontend sẽ tự động được proxy qua Vite, không cần thay đổi code!

### Lưu ý:

1. **URL thay đổi**: Mỗi lần chạy ngrok, URL sẽ khác (free plan)
   - Giải pháp: Mua ngrok plan có fixed domain

2. **Warning page**: Ngrok free có warning page khi truy cập lần đầu
   - Click "Visit Site" để tiếp tục

3. **CORS**: Đảm bảo Backend cho phép CORS từ ngrok domain

---

## ⚙️ PHƯƠNG PHÁP 2: REVERSE PROXY VỚI NGINX/APACHE

### Ưu điểm:
- ✅ URL cố định
- ✅ Không phụ thuộc dịch vụ bên ngoài
- ✅ Hiệu suất tốt
- ✅ Có thể cấu hình SSL/HTTPS

### Nhược điểm:
- ⚠️ Cần cài đặt và cấu hình server
- ⚠️ Phức tạp hơn

### Cấu hình Nginx

#### Bước 1: Cài đặt Nginx

**Windows:**
- Tải từ: http://nginx.org/en/download.html
- Hoặc sử dụng XAMPP (đã có sẵn)

**Linux:**
```bash
sudo apt update
sudo apt install nginx
```

**Mac:**
```bash
brew install nginx
```

#### Bước 2: Tạo file cấu hình

Tạo file `/etc/nginx/sites-available/cho-do-cu` (Linux) hoặc trong thư mục nginx (Windows):

```nginx
server {
    listen 80;
    server_name your-domain.com;  # Hoặc IP của bạn

    # Frontend
    location / {
        proxy_pass http://127.0.0.1:5173;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Backend API
    location /api {
        proxy_pass http://127.0.0.1:8000/api;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # CORS headers
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type' always;
        
        if ($request_method = 'OPTIONS') {
            return 204;
        }
    }

    # Chatbox
    location /chatbox {
        proxy_pass http://127.0.0.1:5000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # CORS headers
        add_header 'Access-Control-Allow-Origin' '*' always;
    }
}
```

#### Bước 3: Kích hoạt cấu hình

**Linux:**
```bash
sudo ln -s /etc/nginx/sites-available/cho-do-cu /etc/nginx/sites-enabled/
sudo nginx -t  # Kiểm tra cấu hình
sudo systemctl restart nginx
```

**Windows:**
- Copy file cấu hình vào thư mục nginx
- Restart nginx service

#### Bước 4: Truy cập

Truy cập: `http://your-domain.com` hoặc `http://[IP_CUA_BAN]`

Tất cả requests sẽ được route tự động:
- `/` → Frontend (port 5173)
- `/api/*` → Backend (port 8000)
- `/chatbox/*` → Chatbox (port 5000)

---

## 🔧 PHƯƠNG PHÁP 3: CẤU HÌNH VITE PROXY (Đơn giản nhất cho LAN)

Phương pháp này chỉ hoạt động trong mạng LAN, nhưng không cần cài thêm gì.

### Bước 1: Cập nhật vite.config.js

```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: false,
    proxy: {
      // Proxy tất cả API requests
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      // Proxy chatbox
      '/chatbox': {
        target: 'http://127.0.0.1:5000',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/chatbox/, ''),
      },
    },
  },
})
```

### Bước 2: Cập nhật Frontend code

Thay đổi tất cả API calls từ:
```javascript
// Cũ
const API_BASE_URL = 'http://127.0.0.1:8000/api/client'
```

Thành:
```javascript
// Mới - Sử dụng relative URL
const API_BASE_URL = '/api/client'
```

### Bước 3: Chạy services

```bash
# Terminal 1: Backend
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Frontend
cd FE_Second-hand-Goods-Trading-Platform
npm run dev

# Terminal 3: Chatbox
cd chatbox
python app.py
```

### Bước 4: Chia sẻ URL

Chỉ cần chia sẻ 1 URL: `http://[IP_CUA_BAN]:5173`

Tất cả API calls sẽ tự động được proxy!

---

## 📊 SO SÁNH CÁC PHƯƠNG PHÁP

| Tiêu chí | ngrok | Nginx/Apache | Vite Proxy |
|----------|-------|--------------|------------|
| **Độ khó** | ⭐ Dễ | ⭐⭐⭐ Khó | ⭐⭐ Trung bình |
| **URL cố định** | ❌ (free) | ✅ | ✅ |
| **HTTPS** | ✅ Tự động | ⚠️ Cần cấu hình | ❌ |
| **Chia sẻ Internet** | ✅ | ⚠️ Cần domain/IP public | ❌ Chỉ LAN |
| **Hiệu suất** | ⚠️ Phụ thuộc ngrok | ✅ Tốt | ✅ Tốt |
| **Chi phí** | Free (có giới hạn) | Free | Free |

### Khuyến nghị:

- **Development/Testing nhanh**: Dùng **ngrok**
- **Production/LAN**: Dùng **Nginx/Apache**
- **Chia sẻ trong mạng nội bộ**: Dùng **Vite Proxy**

---

## 🎯 HƯỚNG DẪN CHI TIẾT: VITE PROXY (Khuyên dùng cho LAN)

Đây là cách đơn giản nhất để chia sẻ trong mạng LAN.

### Bước 1: Cập nhật vite.config.js

File đã được cập nhật sẵn với proxy configuration.

### Bước 2: Cập nhật Frontend để dùng relative URLs

Tìm và thay thế trong các file Vue component:

**Tìm:**
```javascript
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'
```

**Thay bằng:**
```javascript
// Sử dụng relative URL - sẽ được proxy tự động
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/client'
```

### Bước 3: Cập nhật Chatbox URL trong Frontend

Nếu Frontend gọi Chatbox, cập nhật thành:
```javascript
// Thay vì: http://127.0.0.1:5000/chat
// Dùng: /chatbox/chat
```

### Bước 4: Chạy và chia sẻ

```bash
# Chạy tất cả services
# Terminal 1: Backend
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Frontend
cd FE_Second-hand-Goods-Trading-Platform
npm run dev

# Terminal 3: Chatbox
cd chatbox
python app.py
```

**Chia sẻ URL duy nhất**: `http://[IP_CUA_BAN]:5173`

Ví dụ: `http://192.168.1.100:5173`

---

## ✅ KIỂM TRA

### Test từ máy khác:

1. **Frontend**: `http://[IP]:5173`
2. **API qua proxy**: `http://[IP]:5173/api/client/san-pham`
3. **Chatbox qua proxy**: `http://[IP]:5173/chatbox/`

Tất cả đều hoạt động qua 1 URL duy nhất! ✅

---

## 🔒 BẢO MẬT

### Khi chia sẻ qua Internet (ngrok):

1. ⚠️ **Không chia sẻ URL công khai** nếu chưa có authentication
2. ⚠️ **Đổi mật khẩu database** trước khi chia sẻ
3. ⚠️ **Sử dụng HTTPS** (ngrok tự động có)
4. ⚠️ **Giới hạn thời gian** chia sẻ

### Khi chia sẻ trong LAN:

1. ✅ An toàn hơn vì chỉ trong mạng nội bộ
2. ✅ Vẫn nên có authentication
3. ⚠️ Không chia sẻ mật khẩu database

---

## 🎉 HOÀN TẤT!

Bây giờ bạn có thể chia sẻ web qua **1 đường link duy nhất**!

**Chọn phương pháp phù hợp:**
- 🚀 **Nhanh nhất**: ngrok
- ⚙️ **Chuyên nghiệp**: Nginx/Apache
- 🔧 **Đơn giản (LAN)**: Vite Proxy

**Happy Sharing!** 🔗

