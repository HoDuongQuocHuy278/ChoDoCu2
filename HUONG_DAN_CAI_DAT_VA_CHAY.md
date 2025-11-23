# 📚 HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY DỰ ÁN HOÀN CHỈNH

> Hướng dẫn chi tiết từ cài đặt thư viện đến chạy toàn bộ hệ thống

---

## 📋 MỤC LỤC

1. [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
2. [Cài đặt công cụ cần thiết](#-cài-đặt-công-cụ-cần-thiết)
3. [Clone repository](#-clone-repository)
4. [Cài đặt Backend (Laravel)](#-cài-đặt-backend-laravel)
5. [Cài đặt Frontend (Vue.js)](#-cài-đặt-frontend-vuejs)
6. [Cài đặt Chatbox (Python)](#-cài-đặt-chatbox-python)
7. [Cấu hình Database](#-cấu-hình-database)
8. [Chạy dự án](#-chạy-dự-án)
9. [Kiểm tra hệ thống](#-kiểm-tra-hệ-thống)
10. [Xử lý lỗi thường gặp](#-xử-lý-lỗi-thường-gặp)

---

## 💻 YÊU CẦU HỆ THỐNG

### Phần mềm cần cài đặt:

| Phần mềm | Phiên bản tối thiểu | Mục đích |
|----------|---------------------|----------|
| **PHP** | >= 8.2 | Backend Laravel |
| **Composer** | >= 2.x | Quản lý dependencies PHP |
| **Node.js** | >= 16.x | Frontend Vue.js |
| **npm** | >= 8.x | Package manager cho Node.js |
| **Python** | >= 3.7 | Chatbox AI |
| **MySQL** | >= 8.0 | Database |
| **Git** | Latest | Clone repository |

### PHP Extensions cần thiết:
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- BCMath PHP Extension

---

## 🛠️ CÀI ĐẶT CÔNG CỤ CẦN THIẾT

### 1. Cài đặt PHP và Composer

#### Windows:
1. Tải XAMPP từ: https://www.apachefriends.org/
2. Cài đặt XAMPP (đã bao gồm PHP, MySQL, Apache)
3. Tải Composer từ: https://getcomposer.org/download/
4. Chạy file `Composer-Setup.exe` và làm theo hướng dẫn

#### Linux (Ubuntu/Debian):
```bash
# Cài đặt PHP và extensions
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath

# Cài đặt Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Mac:
```bash
# Sử dụng Homebrew
brew install php@8.2
brew install composer
```

### 2. Cài đặt Node.js và npm

#### Windows:
1. Tải Node.js từ: https://nodejs.org/
2. Chọn phiên bản LTS (Long Term Support)
3. Cài đặt và làm theo hướng dẫn
4. npm sẽ được cài đặt tự động cùng Node.js

#### Linux:
```bash
# Sử dụng nvm (khuyên dùng)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
nvm use 18
```

#### Mac:
```bash
brew install node
```

### 3. Cài đặt Python

#### Windows:
1. Tải Python từ: https://www.python.org/downloads/
2. Chọn phiên bản 3.9 trở lên
3. **Quan trọng**: Tích chọn "Add Python to PATH" khi cài đặt

#### Linux:
```bash
sudo apt update
sudo apt install python3 python3-pip python3-venv
```

#### Mac:
```bash
brew install python3
```

### 4. Cài đặt MySQL

#### Windows:
- XAMPP đã bao gồm MySQL, hoặc tải riêng từ: https://dev.mysql.com/downloads/

#### Linux:
```bash
sudo apt install mysql-server
sudo mysql_secure_installation
```

#### Mac:
```bash
brew install mysql
```

### 5. Kiểm tra cài đặt

Mở Terminal/Command Prompt và chạy:

```bash
# Kiểm tra PHP
php -v
# Kết quả: PHP 8.2.x (cli)

# Kiểm tra Composer
composer --version
# Kết quả: Composer version 2.x.x

# Kiểm tra Node.js
node -v
# Kết quả: v18.x.x

# Kiểm tra npm
npm -v
# Kết quả: 9.x.x

# Kiểm tra Python
python --version
# Kết quả: Python 3.9.x

# Kiểm tra MySQL
mysql --version
# Kết quả: mysql Ver 8.0.x
```

---

## 📥 CLONE REPOSITORY

### 1. Clone từ GitHub

```bash
# Clone repository
git clone https://github.com/HoDuongQuocHuy278/ChoDoCu2.git

# Di chuyển vào thư mục dự án
cd ChoDoCu2
```

### 2. Cấu trúc thư mục

Sau khi clone, bạn sẽ thấy cấu trúc:

```
ChoDoCu2/
├── BE_Second-hand-Goods-Trading-Platform/    # Backend Laravel
├── FE_Second-hand-Goods-Trading-Platform/     # Frontend Vue.js
├── chatbox/                                   # Chatbot AI
├── vnpay_php/                                 # VNPay integration
├── README.md
└── PROJECT_STRUCTURE.md
```

---

## ⚙️ CÀI ĐẶT BACKEND (LARAVEL)

### Bước 1: Di chuyển vào thư mục Backend

```bash
cd BE_Second-hand-Goods-Trading-Platform
```

### Bước 2: Cài đặt dependencies PHP

```bash
# Cài đặt tất cả packages từ composer.json
composer install
```

**Lưu ý**: Quá trình này có thể mất 5-10 phút tùy vào tốc độ internet.

### Bước 3: Cấu hình môi trường

```bash
# Copy file .env.example thành .env
copy .env.example .env    # Windows
# hoặc
cp .env.example .env      # Linux/Mac
```

### Bước 4: Tạo Application Key

```bash
php artisan key:generate
```

### Bước 5: Cấu hình Database trong file `.env`

Mở file `.env` và cập nhật thông tin database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cho_do_cu_db
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://127.0.0.1:8000
```

**Lưu ý**: 
- `DB_DATABASE`: Tên database bạn sẽ tạo (xem phần Database)
- `DB_USERNAME`: Thường là `root` cho XAMPP
- `DB_PASSWORD`: Để trống nếu dùng XAMPP mặc định

### Bước 6: Tạo Database

#### Cách 1: Sử dụng phpMyAdmin (XAMPP)
1. Mở http://localhost/phpmyadmin
2. Click "New" để tạo database mới
3. Đặt tên: `cho_do_cu_db`
4. Chọn Collation: `utf8mb4_unicode_ci`
5. Click "Create"

#### Cách 2: Sử dụng MySQL Command Line
```bash
mysql -u root -p
```

Sau đó trong MySQL:
```sql
CREATE DATABASE cho_do_cu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Bước 7: Chạy Migrations và Seeders

```bash
# Chạy migrations và seeders (tạo bảng + dữ liệu mẫu)
php artisan migrate:fresh --seed
```

**Lưu ý**: 
- `migrate:fresh` sẽ xóa tất cả bảng và tạo lại từ đầu
- `--seed` sẽ chèn dữ liệu mẫu vào database

### Bước 8: Tạo Symlink cho Storage

```bash
# Tạo symlink để truy cập hình ảnh từ public/storage
php artisan storage:link
```

### Bước 9: Kiểm tra Backend

```bash
# Chạy Laravel development server
php artisan serve
```

Mở browser và truy cập: **http://127.0.0.1:8000**

Nếu thấy trang Laravel mặc định, Backend đã cài đặt thành công! ✅

**Dừng server**: Nhấn `Ctrl + C` trong terminal

---

## 🎨 CÀI ĐẶT FRONTEND (VUE.JS)

### Bước 1: Di chuyển vào thư mục Frontend

```bash
# Từ thư mục gốc dự án
cd FE_Second-hand-Goods-Trading-Platform
```

### Bước 2: Cài đặt dependencies Node.js

```bash
# Cài đặt tất cả packages từ package.json
npm install
```

**Lưu ý**: Quá trình này có thể mất 5-10 phút.

### Bước 3: Cấu hình môi trường (nếu cần)

Tạo file `.env` trong thư mục Frontend (nếu chưa có):

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api/client
```

**Lưu ý**: File này có thể không cần thiết nếu đã cấu hình trong code.

### Bước 4: Kiểm tra Frontend

```bash
# Chạy development server
npm run dev
```

Mở browser và truy cập: **http://localhost:5173**

Nếu thấy giao diện ứng dụng, Frontend đã cài đặt thành công! ✅

**Dừng server**: Nhấn `Ctrl + C` trong terminal

---

## 🤖 CÀI ĐẶT CHATBOX (PYTHON)

### Bước 1: Di chuyển vào thư mục Chatbox

```bash
# Từ thư mục gốc dự án
cd chatbox
```

### Bước 2: Tạo Virtual Environment (Khuyên dùng)

#### Windows:
```bash
# Tạo virtual environment
python -m venv venv

# Kích hoạt virtual environment
venv\Scripts\activate
```

#### Linux/Mac:
```bash
# Tạo virtual environment
python3 -m venv venv

# Kích hoạt virtual environment
source venv/bin/activate
```

Sau khi kích hoạt, bạn sẽ thấy `(venv)` ở đầu dòng lệnh.

### Bước 3: Cài đặt dependencies Python

```bash
# Cài đặt tất cả packages từ requirements.txt
pip install -r requirements.txt
```

**Lưu ý**: Quá trình này có thể mất 5-10 phút.

### Bước 4: Download NLTK Data

```bash
# Download dữ liệu cần thiết cho NLTK
python -c "import nltk; nltk.download('punkt')"
```

### Bước 5: Train Model (Nếu chưa có file data.pth)

```bash
# Train model AI cho chatbot
python train.py
```

**Lưu ý**: 
- Quá trình train có thể mất 1-2 phút
- File `data.pth` sẽ được tạo sau khi train xong
- Nếu đã có file `data.pth`, có thể bỏ qua bước này

### Bước 6: Kiểm tra Chatbox

```bash
# Chạy Flask server
python app.py
```

Mở browser và truy cập: **http://127.0.0.1:5000**

Bạn sẽ thấy JSON response:
```json
{
  "message": "Chatbot Chợ Đồ Cũ API is running!",
  "bot_name": "Chatbot Chợ Đồ Cũ",
  "version": "1.0.0"
}
```

Nếu thấy response trên, Chatbox đã cài đặt thành công! ✅

**Dừng server**: Nhấn `Ctrl + C` trong terminal

---

## 🗄️ CẤU HÌNH DATABASE

### Tạo Database (Nếu chưa tạo)

Xem lại phần [Bước 6: Tạo Database](#bước-6-tạo-database) trong cài đặt Backend.

### Kiểm tra kết nối Database

Trong file `.env` của Backend, đảm bảo thông tin đúng:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cho_do_cu_db
DB_USERNAME=root
DB_PASSWORD=
```

### Test kết nối

```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan migrate:status
```

Nếu không có lỗi, database đã kết nối thành công! ✅

---

## 🚀 CHẠY DỰ ÁN

Bây giờ bạn cần chạy **3 services** cùng lúc:

### Terminal 1: Backend (Laravel)

```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve
```

**URL**: http://127.0.0.1:8000

### Terminal 2: Frontend (Vue.js)

```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```

**URL**: http://localhost:5173

### Terminal 3: Chatbox (Python)

```bash
cd chatbox

# Kích hoạt virtual environment (nếu dùng)
venv\Scripts\activate    # Windows
# hoặc
source venv/bin/activate  # Linux/Mac

python app.py
```

**URL**: http://127.0.0.1:5000

---

## 🌐 CHIA SẺ WEB CHO NGƯỜI CÙNG MẠNG

Để cho phép các thiết bị khác trong cùng mạng LAN truy cập vào web của bạn:

### Bước 1: Lấy IP Local của máy bạn

#### Windows:
```bash
# Cách 1: Sử dụng ipconfig
ipconfig

# Tìm dòng "IPv4 Address" trong phần "Ethernet adapter" hoặc "Wireless LAN adapter"
# Ví dụ: IPv4 Address. . . . . . . . . . . . : 192.168.1.100
```

#### Linux/Mac:
```bash
# Linux
ip addr show
# hoặc
hostname -I

# Mac
ifconfig | grep "inet "
```

**Lưu ý**: IP thường có dạng `192.168.x.x` hoặc `10.0.x.x`

### Bước 2: Chạy các services với host 0.0.0.0

#### Terminal 1: Backend (Laravel)
```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve --host=0.0.0.0 --port=8000
```

**URL truy cập từ máy khác**: `http://[IP_CUA_BAN]:8000`
**Ví dụ**: `http://192.168.1.100:8000`

#### Terminal 2: Frontend (Vue.js)
```bash
cd FE_Second-hand-Goods-Trading-Platform
npm run dev
```

**Lưu ý**: File `vite.config.js` đã được cấu hình để chạy trên `0.0.0.0`

**URL truy cập từ máy khác**: `http://[IP_CUA_BAN]:5173`
**Ví dụ**: `http://192.168.1.100:5173`

#### Terminal 3: Chatbox (Python)
```bash
cd chatbox
venv\Scripts\activate    # Windows (nếu dùng venv)
python app.py
```

**Lưu ý**: Chatbox đã được cấu hình để chạy trên `0.0.0.0:5000`

**URL truy cập từ máy khác**: `http://[IP_CUA_BAN]:5000`
**Ví dụ**: `http://192.168.1.100:5000`

### Bước 3: Cấu hình Firewall (Nếu cần)

#### Windows:
1. Mở **Windows Defender Firewall**
2. Click **Advanced settings**
3. Click **Inbound Rules** → **New Rule**
4. Chọn **Port** → **Next**
5. Chọn **TCP** và nhập ports: `8000, 5173, 5000`
6. Chọn **Allow the connection** → **Next**
7. Áp dụng cho tất cả profiles → **Next**
8. Đặt tên: "Cho Do Cu Web" → **Finish**

Hoặc sử dụng PowerShell (chạy với quyền Administrator):
```powershell
New-NetFirewallRule -DisplayName "Cho Do Cu Backend" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "Cho Do Cu Frontend" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "Cho Do Cu Chatbox" -Direction Inbound -LocalPort 5000 -Protocol TCP -Action Allow
```

#### Linux:
```bash
# Ubuntu/Debian
sudo ufw allow 8000/tcp
sudo ufw allow 5173/tcp
sudo ufw allow 5000/tcp
```

#### Mac:
Firewall thường tự động cho phép. Nếu cần:
1. System Preferences → Security & Privacy → Firewall
2. Click **Firewall Options**
3. Thêm các ứng dụng cần thiết

### Bước 4: Cập nhật URL trong Frontend (Nếu cần)

Nếu Frontend cần gọi API từ máy khác, cập nhật file `.env` trong Frontend:

```env
VITE_API_BASE_URL=http://[IP_CUA_BAN]:8000/api/client
```

**Ví dụ**: `VITE_API_BASE_URL=http://192.168.1.100:8000/api/client`

### Bước 5: Kiểm tra từ máy khác

1. Đảm bảo máy khác cùng mạng WiFi/LAN với bạn
2. Mở browser trên máy khác
3. Truy cập:
   - Frontend: `http://[IP_CUA_BAN]:5173`
   - Backend API: `http://[IP_CUA_BAN]:8000/api/client/san-pham`
   - Chatbox: `http://[IP_CUA_BAN]:5000`

### Lưu ý quan trọng:

⚠️ **Bảo mật**:
- Chỉ chia sẻ trong mạng nội bộ (LAN)
- Không chia sẻ ra internet công cộng
- Tắt các services khi không sử dụng

⚠️ **IP động**:
- IP có thể thay đổi mỗi lần kết nối WiFi
- Nếu IP thay đổi, cần cập nhật lại URL

⚠️ **Tốc độ**:
- Tốc độ phụ thuộc vào băng thông mạng LAN
- Đảm bảo kết nối WiFi/LAN ổn định

---

## ✅ KIỂM TRA HỆ THỐNG

### 1. Kiểm tra Backend API

Mở browser và truy cập:
- **http://127.0.0.1:8000/api/client/san-pham**

Nếu thấy JSON response với danh sách sản phẩm, Backend hoạt động tốt! ✅

### 2. Kiểm tra Frontend

Mở browser và truy cập:
- **http://localhost:5173**

Nếu thấy giao diện trang chủ, Frontend hoạt động tốt! ✅

### 3. Kiểm tra Chatbox

Mở browser và truy cập:
- **http://127.0.0.1:5000**

Nếu thấy JSON response, Chatbox hoạt động tốt! ✅

### 4. Test Chatbox API

Sử dụng PowerShell hoặc curl:

```powershell
# PowerShell
Invoke-RestMethod -Uri "http://127.0.0.1:5000/chat" -Method POST -ContentType "application/json" -Body '{"message": "Xin chào"}'
```

```bash
# Linux/Mac
curl -X POST http://127.0.0.1:5000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Xin chào"}'
```

---

## 🔧 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi 1: "composer: command not found"

**Nguyên nhân**: Composer chưa được cài đặt hoặc chưa thêm vào PATH.

**Giải pháp**:
- Windows: Cài đặt lại Composer và đảm bảo tích chọn "Add to PATH"
- Linux/Mac: Thêm Composer vào PATH hoặc sử dụng `php composer.phar` thay vì `composer`

### Lỗi 2: "npm: command not found"

**Nguyên nhân**: Node.js chưa được cài đặt hoặc chưa thêm vào PATH.

**Giải pháp**:
- Cài đặt lại Node.js từ https://nodejs.org/
- Đảm bảo tích chọn "Add to PATH" khi cài đặt

### Lỗi 3: "SQLSTATE[HY000] [1045] Access denied"

**Nguyên nhân**: Thông tin đăng nhập database sai.

**Giải pháp**:
1. Kiểm tra lại file `.env` trong Backend
2. Đảm bảo `DB_USERNAME` và `DB_PASSWORD` đúng
3. Với XAMPP, thường là `root` và để trống password

### Lỗi 4: "SQLSTATE[HY000] [2002] No connection could be made"

**Nguyên nhân**: MySQL chưa được khởi động.

**Giải pháp**:
- Windows (XAMPP): Mở XAMPP Control Panel và Start MySQL
- Linux: `sudo systemctl start mysql`
- Mac: `brew services start mysql`

### Lỗi 5: "Port 8000 already in use"

**Nguyên nhân**: Port 8000 đã được sử dụng bởi ứng dụng khác.

**Giải pháp**:
```bash
# Chạy trên port khác
php artisan serve --port=8001
```

### Lỗi 6: "Port 5173 already in use"

**Nguyên nhân**: Port 5173 đã được sử dụng.

**Giải pháp**:
- Vite sẽ tự động chuyển sang port khác (5174, 5175...)
- Hoặc chỉ định port trong `vite.config.js`

### Lỗi 7: "Module not found" trong Python

**Nguyên nhân**: Chưa cài đặt dependencies hoặc chưa kích hoạt virtual environment.

**Giải pháp**:
```bash
# Kích hoạt virtual environment
venv\Scripts\activate    # Windows
source venv/bin/activate  # Linux/Mac

# Cài đặt lại dependencies
pip install -r requirements.txt
```

### Lỗi 8: "Storage link already exists"

**Nguyên nhân**: Symlink đã được tạo trước đó.

**Giải pháp**:
```bash
# Xóa symlink cũ và tạo lại
php artisan storage:link --force
```

### Lỗi 9: "Class 'App\Models\...' not found"

**Nguyên nhân**: Autoload chưa được cập nhật.

**Giải pháp**:
```bash
composer dump-autoload
```

### Lỗi 10: "Permission denied" khi upload hình ảnh

**Nguyên nhân**: Thư mục storage không có quyền ghi.

**Giải pháp**:
```bash
# Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Windows: Kiểm tra quyền thư mục trong Properties
```

---

## 📝 LỆNH NHANH TÓM TẮT

### Cài đặt lần đầu (từ đầu)

```bash
# 1. Backend
cd BE_Second-hand-Goods-Trading-Platform
composer install
copy .env.example .env
php artisan key:generate
# Cấu hình .env (database)
php artisan migrate:fresh --seed
php artisan storage:link

# 2. Frontend
cd ../FE_Second-hand-Goods-Trading-Platform
npm install

# 3. Chatbox
cd ../chatbox
python -m venv venv
venv\Scripts\activate    # Windows
pip install -r requirements.txt
python -c "import nltk; nltk.download('punkt')"
python train.py
```

### Chạy dự án (mỗi lần)

```bash
# Terminal 1: Backend
cd BE_Second-hand-Goods-Trading-Platform
php artisan serve

# Terminal 2: Frontend
cd FE_Second-hand-Goods-Trading-Platform
npm run dev

# Terminal 3: Chatbox
cd chatbox
venv\Scripts\activate    # Windows (nếu dùng venv)
python app.py
```

### Reset Database (nếu cần)

```bash
cd BE_Second-hand-Goods-Trading-Platform
php artisan migrate:fresh --seed
```

---

## 🎯 TÀI KHOẢN MẶC ĐỊNH (Từ Seeder)

Sau khi chạy `php artisan migrate:fresh --seed`, bạn sẽ có:

### Admin:
- Email: `admin@example.com`
- Password: `password`

### User thường:
- Email: `user@example.com`
- Password: `password`

### Seller:
- Email: `seller@example.com`
- Password: `password`

**Lưu ý**: Đổi mật khẩu ngay sau khi đăng nhập lần đầu!

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề không giải quyết được:

1. Kiểm tra lại các bước trong hướng dẫn
2. Xem file `README.md` và `PROJECT_STRUCTURE.md`
3. Kiểm tra logs:
   - Backend: `BE_Second-hand-Goods-Trading-Platform/storage/logs/laravel.log`
   - Chatbox: Xem output trong terminal

---

## 🎉 HOÀN TẤT!

Chúc mừng! Bạn đã cài đặt và chạy thành công dự án **Second-hand Goods Trading Platform**! 🎊

Bây giờ bạn có thể:
- ✅ Truy cập Frontend tại: http://localhost:5173
- ✅ Sử dụng API Backend tại: http://127.0.0.1:8000/api/client
- ✅ Test Chatbox tại: http://127.0.0.1:5000

**Happy Coding!** 🚀

