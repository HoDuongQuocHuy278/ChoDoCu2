# Chatbot Chợ Đồ Cũ

Chatbot AI hỗ trợ khách hàng cho website Chợ Đồ Cũ - nền tảng mua bán đồ cũ.

## Tính năng

- Trả lời tự động các câu hỏi về sản phẩm, mua bán, thanh toán, giao hàng
- Hỗ trợ tìm kiếm sản phẩm
- Hướng dẫn mua hàng và bán hàng
- Thông tin về thanh toán và vận chuyển
- Chính sách đổi trả

## Cài đặt

1. Cài đặt Python dependencies:
```bash
pip install torch flask flask-cors nltk numpy
```

2. Download NLTK data:
```python
python
>>> import nltk
>>> nltk.download('punkt')
>>> exit()
```

3. Train model (nếu chưa có model):
```bash
python train.py
```

4. Chạy chatbot API:
```bash
python app.py
```

Chatbot sẽ chạy tại `http://127.0.0.1:5000`

## API Endpoints

- `POST /chat` hoặc `POST /chatbot` - Gửi tin nhắn đến chatbot
  - Request: `{ "message": "Xin chào" }`
  - Response: `{ "reply": "Xin chào! Chào mừng bạn đến với Chợ Đồ Cũ...", "tag": "chao_hoi" }`

- `GET /` hoặc `GET /health` - Kiểm tra trạng thái API

## Intents

Chatbot hỗ trợ các chủ đề:
- Chào hỏi
- Tìm sản phẩm
- Giá cả
- Mua hàng
- Bán hàng
- Thanh toán
- Giao hàng
- Đổi trả
- Chất lượng
- Đơn hàng
- Hướng dẫn
- Cảm ơn/Tạm biệt

## Tích hợp với Frontend

Frontend Vue.js sẽ gọi API tại:
- URL: `http://127.0.0.1:5000/chat`
- Method: POST
- Body: `{ "message": "..." }`

Bạn có thể cấu hình URL trong file `.env`:
```
VITE_CHATBOT_URL=http://127.0.0.1:5000
```

## Cập nhật Intents

Sửa file `intents.json` để thêm/cập nhật các câu hỏi và câu trả lời, sau đó train lại model:

```bash
python train.py
```

## Lưu ý

- Model được lưu trong file `data.pth`
- Cần train lại model khi cập nhật `intents.json`
- Chatbot hỗ trợ tiếng Việt với NLTK tokenizer

