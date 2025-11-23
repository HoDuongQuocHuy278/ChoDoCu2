# Hướng dẫn chạy Chatbot Chợ Đồ Cũ

## Cách 1: Chạy trực tiếp (Nhanh nhất)

### Bước 1: Di chuyển vào thư mục chatbox
```bash
cd C:\xampp\htdocs\Shopee\chatbox
```

### Bước 2: Chạy chatbot
```bash
python app.py
```

Chatbot sẽ chạy tại: **http://127.0.0.1:5000**

---

## Cách 2: Sử dụng script tự động (Khuyên dùng)

### Windows:
```bash
cd C:\xampp\htdocs\Shopee\chatbox
start.bat
```

### Linux/Mac:
```bash
cd /path/to/Shopee/chatbox
chmod +x start.sh
./start.sh
```

Script sẽ tự động:
- Tạo virtual environment (nếu chưa có)
- Cài đặt dependencies
- Download NLTK data
- Train model (nếu chưa có)
- Chạy Flask server

---

## Cách 3: Chạy từ thư mục hiện tại (BE_Second-hand-Goods-Trading-Platform)

Nếu bạn đang ở trong thư mục `BE_Second-hand-Goods-Trading-Platform`:

### Windows PowerShell:
```powershell
cd ..\chatbox
python app.py
```

### Windows CMD:
```cmd
cd ..\chatbox
python app.py
```

### Linux/Mac:
```bash
cd ../chatbox
python3 app.py
```

---

## Cài đặt lần đầu (nếu chưa cài)

```bash
cd C:\xampp\htdocs\Shopee\chatbox

# Cài đặt dependencies
pip install -r requirements.txt

# Download NLTK data
python -c "import nltk; nltk.download('punkt')"

# Train model (nếu chưa có hoặc muốn train lại)
python train.py

# Chạy chatbot
python app.py
```

---

## Kiểm tra chatbot đã chạy

Mở browser và truy cập:
- **http://127.0.0.1:5000** hoặc **http://localhost:5000**

Bạn sẽ thấy:
```json
{
  "message": "Chatbot Chợ Đồ Cũ API is running!",
  "bot_name": "Chatbot Chợ Đồ Cũ",
  "version": "1.0.0"
}
```

---

## Test chatbot qua API

Sử dụng curl hoặc Postman:

```bash
curl -X POST http://127.0.0.1:5000/chat \
  -H "Content-Type: application/json" \
  -d "{\"message\": \"Xin chào\"}"
```

Hoặc PowerShell:
```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:5000/chat" -Method POST -ContentType "application/json" -Body '{"message": "Xin chào"}'
```

---

## Dừng chatbot

Nhấn **Ctrl + C** trong terminal để dừng server.

---

## Lưu ý

1. **Port 5000** phải trống. Nếu port đã được sử dụng, bạn có thể sửa trong `app.py`:
   ```python
   app.run(host="0.0.0.0", port=5001)  # Đổi sang port khác
   ```

2. **Python version**: Cần Python 3.7 trở lên

3. **Model cũ**: Nếu bạn cập nhật `intents.json`, cần train lại:
   ```bash
   python train.py
   ```

4. **Virtual Environment**: Khuyên dùng virtual environment:
   ```bash
   python -m venv venv
   venv\Scripts\activate  # Windows
   source venv/bin/activate  # Linux/Mac
   pip install -r requirements.txt
   ```

