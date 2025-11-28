from flask import Flask, request, jsonify
from flask_cors import CORS
import torch
from model import NeuralNet
from nltk_utils import bag_of_words, tokenize
import json
import random
import requests

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes
# đổi api
# Laravel API base URL
LARAVEL_API_URL = "http://192.168.1.229:8000/api/client"
FRONTEND_URL = "http://192.168.1.229:5173"

# Load intents
with open('intents.json', 'r', encoding='utf-8') as f:
    intents = json.load(f)

# Load model
FILE = "data.pth"
data = torch.load(FILE, map_location=torch.device('cpu'))

input_size = data["input_size"]
hidden_size = data["hidden_size"]
output_size = data["output_size"]
all_words = data["all_words"]
tags = data["tags"]
model_state = data["model_state"]

model = NeuralNet(input_size, hidden_size, output_size)
model.load_state_dict(model_state)
model.eval()

# Bot name for Chợ Đồ Cũ
BOT_NAME = "Chatbot Chợ Đồ Cũ"


def fetch_products(sort='newest', per_page=5, keyword=None, price_min=None, price_max=None):
    """Fetch products from Laravel API"""
    try:
        params = {
            'sort': sort,
            'per_page': per_page,
            'page': 1
        }
        if keyword:
            params['q'] = keyword
        if price_min:
            params['price_min'] = price_min
        if price_max:
            params['price_max'] = price_max
        
        response = requests.get(f"{LARAVEL_API_URL}/san-pham", params=params, timeout=5)
        if response.status_code == 200:
            data = response.json()
            if data.get('status') and data.get('data'):
                products = data['data'].get('data', [])
                return products
        return []
    except Exception as e:
        print(f"Error fetching products: {e}")
        return []


def format_product_message(products, title="Sản phẩm", return_structured=False):
    """Format products into a message with links
    
    Args:
        products: List of product dictionaries
        title: Title for the product list
        return_structured: If True, return structured data instead of text
    
    Returns:
        If return_structured=True: dict with 'text' and 'products'
        If return_structured=False: str message
    """
    if not products:
        message = f"Hiện tại không có {title.lower()} nào. Bạn có thể xem tất cả sản phẩm trên trang danh sách sản phẩm."
        if return_structured:
            return {
                'text': message,
                'products': []
            }
        return message
    
    # Format products for structured response
    formatted_products = []
    for product in products[:5]:  # Limit to 5 products
        product_id = product.get('id')
        name = product.get('name', 'Sản phẩm')
        price = product.get('price', 0)
        image = product.get('image')
        
        # Format price
        price_str = f"{price:,.0f} ₫" if price else "Liên hệ"
        
        # Create product URL
        product_url = f"{FRONTEND_URL}/san-pham/{product_id}"
        
        formatted_products.append({
            'id': product_id,
            'name': name,
            'price': price,
            'price_formatted': price_str,
            'image': image,
            'url': product_url
        })
    
    # Build text message
    message = f"{title}:\n\n"
    for i, product in enumerate(formatted_products, 1):
        message += f"{i}. {product['name']}\n"
        message += f"   💰 Giá: {product['price_formatted']}\n"
        message += f"   🔗 Xem: {product['url']}\n\n"
    
    if len(products) > 5:
        message += f"... và {len(products) - 5} sản phẩm khác.\n\n"
    
    message += "Bạn có thể xem tất cả sản phẩm tại: " + FRONTEND_URL + "/danh-sach-san-pham"
    
    if return_structured:
        return {
            'text': message,
            'products': formatted_products,
            'more_url': FRONTEND_URL + "/danh-sach-san-pham"
        }
    
    return message


def extract_search_params(message):
    """Extract keyword and sort order from user message"""
    message = message.lower()
    
    # Default params
    params = {
        'keyword': None,
        'sort': 'newest'
    }
    
    # Detect sort order
    if any(w in message for w in ['rẻ', 'thấp', 'giá tốt', 'giá rẻ']):
        params['sort'] = 'price_asc'
    elif any(w in message for w in ['cao', 'đắt', 'vip', 'xịn']):
        params['sort'] = 'price_desc'
    elif any(w in message for w in ['mới', 'new', 'vừa đăng']):
        params['sort'] = 'newest'
        
    # Remove stop words to find keyword
    stop_words = [
        'tìm', 'kiếm', 'mua', 'bán', 'xem', 'cho', 'tôi', 'mình', 'shop', 'ơi', 'có', 'không', 
        'sản phẩm', 'đồ', 'hàng', 'cái', 'chiếc', 'loại', 'các', 'những', 'là', 'với', 
        'giá', 'rẻ', 'nhất', 'mới', 'cũ', 'thấp', 'cao', 'đắt', 'khoảng', 'tầm', 'dưới', 'trên'
    ]
    
    words = message.split()
    keywords = [w for w in words if w not in stop_words]
    
    if keywords:
        params['keyword'] = ' '.join(keywords)
        
    return params


@app.route("/chat", methods=["POST"])
@app.route("/chatbot", methods=["POST"])
def chat():
    try:
        data = request.json
        message = data.get("message", "").strip()
        
        if not message:
            return jsonify({
                "reply": "Xin chào! Tôi là chatbot hỗ trợ của Chợ Đồ Cũ. Bạn cần hỗ trợ gì?",
                "error": None
            })
        
        # Tokenize and predict
        sentence = tokenize(message)
        X = bag_of_words(sentence, all_words)
        X = torch.tensor(X, dtype=torch.float32).unsqueeze(0)
        
        # Predict tag
        output = model(X)
        _, predicted = torch.max(output, dim=1)
        tag = tags[predicted.item()]
        
        # Find response
        for intent in intents["intents"]:
            if tag == intent["tag"]:
                # Randomly select a response from available responses
                response_template = random.choice(intent["responses"])
                
                # Handle special product-related intents
                products_data = None
                
                # List of tags that trigger product search
                product_search_tags = ["tim_san_pham", "san_pham_moi", "san_pham_theo_gia", "san_pham_lien_quan"]
                
                if tag in product_search_tags or response_template.startswith("PRODUCTS_"):
                    # Extract params from user message
                    search_params = extract_search_params(message)
                    
                    # Override sort if tag is specific
                    if tag == "san_pham_moi":
                        search_params['sort'] = 'newest'
                    elif tag == "san_pham_theo_gia":
                        # Keep extracted sort or default to price_asc if intent is price-related but no specific direction found
                        if search_params['sort'] == 'newest': 
                            search_params['sort'] = 'price_asc'
                            
                    # Fetch products
                    products = fetch_products(
                        sort=search_params['sort'], 
                        per_page=5, 
                        keyword=search_params['keyword']
                    )
                    
                    # Determine title based on params
                    title = "Sản phẩm"
                    if search_params['keyword']:
                        title += f" '{search_params['keyword']}'"
                    
                    if search_params['sort'] == 'price_asc':
                        title += " (Giá rẻ nhất)"
                    elif search_params['sort'] == 'newest':
                        title += " (Mới nhất)"
                        
                    products_data = format_product_message(products, title, return_structured=True)
                    response = products_data['text']
                else:
                    response = response_template
                
                result = {
                    "reply": response,
                    "error": None,
                    "tag": tag
                }
                
                # Add products data if available
                if products_data and products_data.get('products'):
                    result['products'] = products_data['products']
                    result['more_url'] = products_data.get('more_url')
                
                return jsonify(result)
        
        # Fallback if no intent matches
        return jsonify({
            "reply": "Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn. Bạn có thể hỏi tôi về: sản phẩm, cách mua bán, thanh toán, giao hàng, đổi trả, hoặc đơn hàng.",
            "error": None,
            "tag": "unknown"
        })
    
    except Exception as e:
        return jsonify({
            "reply": "Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau hoặc liên hệ bộ phận hỗ trợ.",
            "error": str(e)
        }), 500


@app.route("/", methods=["GET"])
@app.route("/health", methods=["GET"])
def home():
    return jsonify({
        "message": "Chatbot Chợ Đồ Cũ API is running!",
        "bot_name": BOT_NAME,
        "version": "1.0.0"
    })


if __name__ == "__main__":
    print(f"{BOT_NAME} đã sẵn sàng!")
    print("API running at http://0.0.0.0:5000")
    app.run(host="0.0.0.0", port=5000, debug=True)
