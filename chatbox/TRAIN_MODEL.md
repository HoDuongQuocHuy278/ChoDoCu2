# Hướng dẫn Train lại Model Chatbot

Sau khi cập nhật `intents.json`, bạn cần train lại model để chatbot nhận diện được các câu hỏi mới.

## Bước 1: Cài đặt dependencies

```bash
pip install -r requirements.txt
```

## Bước 2: Cài đặt NLTK data

```python
python
>>> import nltk
>>> nltk.download('punkt_tab')
>>> nltk.download('punkt')
>>> exit()
```

Hoặc chạy trực tiếp:
```bash
python -c "import nltk; nltk.download('punkt_tab'); nltk.download('punkt')"
```

## Bước 3: Train model

```bash
python train.py
```

Quá trình training sẽ chạy trong khoảng vài phút (500 epochs) và tạo ra file `data.pth`.

Bạn sẽ thấy output như:
```
Epoch [100/500], Loss: 0.1234
Epoch [200/500], Loss: 0.0567
...
Training complete.
File data.pth đã lưu xong!
```

## Bước 4: Test chatbot

### Cách 1: Chạy script chat.py (console)
```bash
python chat.py
```

### Cách 2: Chạy Flask API và test qua HTTP
```bash
python app.py
```

Sau đó test qua Postman hoặc curl:
```bash
curl -X POST http://127.0.0.1:5000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Xin chào"}'
```

Hoặc test với các câu hỏi mới:
```bash
# Hỏi về sản phẩm mới
curl -X POST http://127.0.0.1:5000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "có điện thoại mới không"}'

# Hỏi về sản phẩm giá rẻ
curl -X POST http://127.0.0.1:5000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "sản phẩm giá rẻ nhất"}'

# Hỏi về cuộc sống
curl -X POST http://127.0.0.1:5000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "mua đồ cũ có tốt không"}'
```

## Tính năng mới

Sau khi train lại model với các intent mới, chatbot có thể:

1. **Giới thiệu sản phẩm mới**: Khi hỏi "có điện thoại mới không", chatbot sẽ query API và trả về danh sách sản phẩm mới nhất kèm link
2. **Giới thiệu sản phẩm theo giá**: Khi hỏi "sản phẩm giá rẻ nhất", chatbot sẽ trả về sản phẩm sắp xếp theo giá từ thấp đến cao
3. **Tư vấn về cuộc sống**: Hỏi về kinh nghiệm mua đồ cũ, tiết kiệm, vệ sinh đồ cũ, v.v.
4. **Giới thiệu sản phẩm liên quan**: Khi hỏi "giới thiệu sản phẩm", chatbot sẽ trả về các sản phẩm đề xuất

## Lưu ý

- File `data.pth` sẽ được tạo/ghi đè sau khi train xong
- Nếu có lỗi về NLTK, hãy đảm bảo đã download 'punkt_tab' và 'punkt' data
- Training có thể mất vài phút tùy vào số lượng intents (hiện tại ~15 intents)
- Đảm bảo Laravel API đang chạy tại `http://127.0.0.1:8000` để chatbot có thể query sản phẩm
- Frontend URL mặc định là `http://localhost:5173`, bạn có thể chỉnh sửa trong `app.py` nếu cần

## Cấu hình API URL

Nếu Laravel API hoặc Frontend chạy ở port khác, bạn có thể chỉnh sửa trong `app.py`:

```python
LARAVEL_API_URL = "http://127.0.0.1:8000/api/client"
FRONTEND_URL = "http://localhost:5173"
```

