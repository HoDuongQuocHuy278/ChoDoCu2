# 🌐 HƯỚNG DẪN CHIA SẺ WEB CHO NGƯỜI CÙNG MẠNG

> Hướng dẫn chi tiết để cho phép các thiết bị khác trong cùng mạng LAN truy cập vào web của bạn

---

## 📋 MỤC LỤC

1. [Lấy IP Local](#-bước-1-lấy-ip-local-của-máy-bạn)
2. [Chạy Services với Host 0.0.0.0](#-bước-2-chạy-các-services-với-host-0000)
3. [Cấu hình Firewall](#-bước-3-cấu-hình-firewall-nếu-cần)
4. [Cập nhật URL Frontend](#-bước-4-cập-nhật-url-trong-frontend-nếu-cần)
5. [Kiểm tra từ máy khác](#-bước-5-kiểm-tra-từ-máy-khác)
6. [Xử lý lỗi](#-xử-lý-lỗi)

---

## 📍 BƯỚC 1: LẤY IP LOCAL CỦA MÁY BẠN

### Windows:

#### Cách 1: Sử dụng Command Prompt
```bash
ipconfig
```

Tìm dòng **"IPv4 Address"** trong phần:
- **"Ethernet adapter"** (nếu dùng dây mạng)
- **"Wireless LAN adapter Wi-Fi"** (nếu dùng WiFi)

**Ví dụ**:
```
Wireless LAN adapter Wi-Fi:
   IPv4 Address. . . . . . . . . . . . : 192.168.1.100
```

#### Cách 2: Sử dụng PowerShell
```powershell
Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.InterfaceAlias -notlike "*Loopback*"} | Select-Object IPAddress, InterfaceAlias
```

### Linux:

```bash
# Cách 1
ip addr show

# Cách 2
hostname -I

# Cách 3
ifconfig | grep "inet "
```

### Mac:

```bash
# Cách 1
ifconfig | grep "inet "

# Cách 2
ipconfig getifaddr en0    # WiFi
ipconfig getifaddr en1    # Ethernet
```

**Lưu ý**: IP thường có dạng:
- `192.168.x.x` (phổ biến nhất)
- `10.0.x.x`
- `172.16.x.x` đến `172.31.x.x`

---

## 🚀 BƯỚC 2: CHẠY CÁC SERVICES VỚI HOST 0.0.0.0

### Terminal 1: Backend (Laravel)

```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000
```

**Kết quả**:
```
INFO  Server running on [http://0.0.0.0:8000].
```

**URL truy cập từ máy khác**: `http://[IP_CUA_BAN]:8000`

**Ví dụ**: Nếu IP của bạn là `192.168.1.100`:
- URL: `http://192.168.1.100:8000`
- API: `http://192.168.1.100:8000/api/client/san-pham`

---

### Terminal 2: Frontend (Vue.js)

```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```

**Lưu ý**: File `vite.config.js` đã được cấu hình sẵn để chạy trên `0.0.0.0`

**Kết quả**:
```
  VITE v4.4.5  ready in 500 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: http://192.168.1.100:5173/
```

**URL truy cập từ máy khác**: `http://[IP_CUA_BAN]:5173`

**Ví dụ**: `http://192.168.1.100:5173`

---

### Terminal 3: Chatbox (Python)

```bash
cd chatbox

# Kích hoạt virtual environment (nếu dùng)
venv\Scripts\activate    # Windows
# hoặc
source venv/bin/activate  # Linux/Mac

python app.py
```

**Lưu ý**: Chatbox đã được cấu hình sẵn để chạy trên `0.0.0.0:5000`

**Kết quả**:
```
Chatbot Chợ Đồ Cũ đã sẵn sàng!
API running at http://0.0.0.0:5000
 * Running on all addresses (0.0.0.0)
 * Running on http://127.0.0.1:5000
 * Running on http://192.168.1.100:5000
```

**URL truy cập từ máy khác**: `http://[IP_CUA_BAN]:5000`

**Ví dụ**: `http://192.168.1.100:5000`

---

## 🔥 BƯỚC 3: CẤU HÌNH FIREWALL (NẾU CẦN)

Nếu không thể truy cập từ máy khác, có thể do Firewall chặn. Cần mở các port: **8000, 5173, 5000**

### Windows:

#### Cách 1: Sử dụng Windows Defender Firewall (GUI)

1. Mở **Windows Defender Firewall**
   - Nhấn `Win + R` → gõ `firewall.cpl` → Enter
2. Click **Advanced settings**
3. Click **Inbound Rules** → **New Rule**
4. Chọn **Port** → **Next**
5. Chọn **TCP** và nhập ports: `8000, 5173, 5000` → **Next**
6. Chọn **Allow the connection** → **Next**
7. Áp dụng cho tất cả profiles → **Next**
8. Đặt tên: "Cho Do Cu Web" → **Finish**

Lặp lại cho mỗi port nếu cần.

#### Cách 2: Sử dụng PowerShell (Nhanh hơn)

**Chạy PowerShell với quyền Administrator**:

```powershell
# Mở port 8000 (Backend)
New-NetFirewallRule -DisplayName "Cho Do Cu Backend" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow

# Mở port 5173 (Frontend)
New-NetFirewallRule -DisplayName "Cho Do Cu Frontend" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow

# Mở port 5000 (Chatbox)
New-NetFirewallRule -DisplayName "Cho Do Cu Chatbox" -Direction Inbound -LocalPort 5000 -Protocol TCP -Action Allow
```

#### Cách 3: Tắt Firewall tạm thời (Không khuyên dùng)

1. Mở **Windows Defender Firewall**
2. Click **Turn Windows Defender Firewall on or off**
3. Tắt cho **Private network** (tạm thời)
4. **Lưu ý**: Nhớ bật lại sau khi test!

---

### Linux (Ubuntu/Debian):

```bash
# Sử dụng ufw
sudo ufw allow 8000/tcp
sudo ufw allow 5173/tcp
sudo ufw allow 5000/tcp

# Kiểm tra trạng thái
sudo ufw status
```

### Mac:

Firewall của Mac thường tự động cho phép. Nếu cần cấu hình:

1. **System Preferences** → **Security & Privacy** → **Firewall**
2. Click **Firewall Options**
3. Thêm các ứng dụng cần thiết

---

## ⚙️ BƯỚC 4: CẬP NHẬT URL TRONG FRONTEND (NẾU CẦN)

Nếu Frontend cần gọi API từ máy khác, cần cập nhật URL API.

### Tạo/Cập nhật file `.env` trong Frontend:

```bash
cd FE_Second-hand-Goods-Trading-Platform
```

Tạo file `.env` (nếu chưa có):

```env
VITE_API_BASE_URL=http://[IP_CUA_BAN]:8000/api/client
```

**Ví dụ**: Nếu IP của bạn là `192.168.1.100`:
```env
VITE_API_BASE_URL=http://192.168.1.100:8000/api/client
```

**Lưu ý**: 
- Thay `[IP_CUA_BAN]` bằng IP thực tế của bạn
- Sau khi cập nhật, cần **restart** Frontend server (`npm run dev`)

---

## ✅ BƯỚC 5: KIỂM TRA TỪ MÁY KHÁC

### Yêu cầu:
1. ✅ Máy khác phải **cùng mạng WiFi/LAN** với máy bạn
2. ✅ Các services đã chạy với `--host=0.0.0.0`
3. ✅ Firewall đã được cấu hình (nếu cần)

### Cách kiểm tra:

#### 1. Từ máy khác, mở browser và truy cập:

**Frontend**:
```
http://[IP_CUA_BAN]:5173
```
**Ví dụ**: `http://192.168.1.100:5173`

**Backend API**:
```
http://[IP_CUA_BAN]:8000/api/client/san-pham
```
**Ví dụ**: `http://192.168.1.100:8000/api/client/san-pham`

**Chatbox**:
```
http://[IP_CUA_BAN]:5000
```
**Ví dụ**: `http://192.168.1.100:5000`

#### 2. Kiểm tra từ điện thoại:

1. Kết nối điện thoại vào **cùng WiFi** với máy bạn
2. Mở browser trên điện thoại
3. Truy cập: `http://[IP_CUA_BAN]:5173`

**Lưu ý**: Đảm bảo điện thoại và máy tính cùng WiFi!

---

## 🔧 XỬ LÝ LỖI

### Lỗi 1: "This site can't be reached" hoặc "Connection refused"

**Nguyên nhân**: 
- Services chưa chạy với `--host=0.0.0.0`
- Firewall đang chặn
- IP không đúng

**Giải pháp**:
1. Kiểm tra services đã chạy với `--host=0.0.0.0` chưa
2. Kiểm tra Firewall (xem Bước 3)
3. Kiểm tra lại IP: `ipconfig` (Windows) hoặc `hostname -I` (Linux)

### Lỗi 2: "ERR_CONNECTION_TIMED_OUT"

**Nguyên nhân**: 
- Firewall đang chặn
- Máy khác không cùng mạng

**Giải pháp**:
1. Kiểm tra Firewall (xem Bước 3)
2. Đảm bảo máy khác cùng WiFi/LAN
3. Thử ping IP: `ping [IP_CUA_BAN]` từ máy khác

### Lỗi 3: "CORS policy" khi gọi API

**Nguyên nhân**: Backend chưa cho phép CORS từ IP khác

**Giải pháp**: 
- Laravel đã có CORS middleware, nhưng nếu vẫn lỗi:
- Kiểm tra file `config/cors.php` trong Backend
- Đảm bảo `allowed_origins` bao gồm IP của bạn

### Lỗi 4: IP thay đổi mỗi lần kết nối WiFi

**Nguyên nhân**: Router cấp IP động (DHCP)

**Giải pháp**:
1. **Cấu hình IP tĩnh** (Khuyên dùng):
   - Windows: Network Settings → Change adapter options → Properties → IPv4 → Use static IP
   - Đặt IP cố định, ví dụ: `192.168.1.100`
2. Hoặc **kiểm tra IP mỗi lần** trước khi chia sẻ

### Lỗi 5: Chỉ truy cập được từ một số thiết bị

**Nguyên nhân**: 
- Router có tính năng "AP Isolation" bật
- Firewall của router chặn

**Giải pháp**:
1. Tắt "AP Isolation" trong cài đặt router
2. Kiểm tra Firewall của router

---

## 📝 TÓM TẮT LỆNH NHANH

### Chạy cho mạng LAN:

```bash
# Terminal 1: Backend
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Frontend
cd FE_Second-hand-Goods-Trading-Platform
npm run dev

# Terminal 3: Chatbox
cd chatbox
venv\Scripts\activate    # Windows (nếu dùng venv)
python app.py
```

### Lấy IP (Windows):
```bash
ipconfig
```

### Mở Firewall (Windows PowerShell - Admin):
```powershell
New-NetFirewallRule -DisplayName "Cho Do Cu Backend" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "Cho Do Cu Frontend" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "Cho Do Cu Chatbox" -Direction Inbound -LocalPort 5000 -Protocol TCP -Action Allow
```

---

## ⚠️ LƯU Ý BẢO MẬT

1. **Chỉ chia sẻ trong mạng nội bộ (LAN)**
   - Không chia sẻ ra internet công cộng
   - Chỉ dùng trong mạng gia đình/văn phòng

2. **Tắt services khi không sử dụng**
   - Nhấn `Ctrl + C` để dừng các services
   - Không để chạy 24/7 nếu không cần

3. **Không chia sẻ mật khẩu database**
   - Giữ file `.env` an toàn
   - Không commit `.env` lên Git

4. **Sử dụng HTTPS trong production**
   - Hướng dẫn này chỉ cho development
   - Production cần cấu hình HTTPS/SSL

---

## 🎉 HOÀN TẤT!

Bây giờ bạn đã có thể chia sẻ web cho người cùng mạng! 

**URL truy cập từ máy khác**:
- Frontend: `http://[IP_CUA_BAN]:5173`
- Backend: `http://[IP_CUA_BAN]:8000`
- Chatbox: `http://[IP_CUA_BAN]:5000`

**Happy Sharing!** 🌐

