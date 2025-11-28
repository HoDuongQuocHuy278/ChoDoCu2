import hashlib
import urllib.parse
import datetime
import random

vnp_TmnCode = "MUAP6QM1"
vnp_HashSecret = "QPMYQVFJSIV5UIIC5RF1U8HKPDLHI21D"
vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"
vnp_ReturnUrl = "http://192.168.1.61:8000/api/client/payment/vnpay/callback"

# Generate params
vnp_TxnRef = datetime.datetime.now().strftime("%Y%m%d%H%M%S")
vnp_OrderInfo = "Thanh toan test Python"
vnp_Amount = 10000 * 100
vnp_CreateDate = datetime.datetime.now().strftime("%Y%m%d%H%M%S")
vnp_IpAddr = "127.0.0.1"

inputData = {
    "vnp_Version": "2.1.0",
    "vnp_TmnCode": vnp_TmnCode,
    "vnp_Amount": str(vnp_Amount),
    "vnp_Command": "pay",
    "vnp_CreateDate": vnp_CreateDate,
    "vnp_CurrCode": "VND",
    "vnp_IpAddr": vnp_IpAddr,
    "vnp_Locale": "vn",
    "vnp_OrderInfo": vnp_OrderInfo,
    "vnp_OrderType": "other",
    "vnp_ReturnUrl": vnp_ReturnUrl,
    "vnp_TxnRef": vnp_TxnRef,
}

# Sort parameters
sorted_data = sorted(inputData.items())

# Build query string and hash data
query_parts = []
hash_data_parts = []
for key, value in sorted_data:
    encoded_key = urllib.parse.quote_plus(key)
    encoded_value = urllib.parse.quote_plus(str(value))
    query_parts.append(f"{encoded_key}={encoded_value}")
    hash_data_parts.append(f"{encoded_key}={encoded_value}")

query_string = "&".join(query_parts)
hash_data = "&".join(hash_data_parts)

# Calculate hash
secure_hash = hashlib.sha512(vnp_HashSecret.encode('utf-8')).hexdigest()
# VNPay uses HMAC-SHA512
secure_hash = hashlib.new('sha512', hash_data.encode('utf-8'))
# Wait, python hashlib.new doesn't take key. It's hmac.
import hmac
secure_hash = hmac.new(vnp_HashSecret.encode('utf-8'), hash_data.encode('utf-8'), hashlib.sha512).hexdigest()

final_url = f"{vnp_Url}?{query_string}&vnp_SecureHash={secure_hash}"

print("GENERATED_URL_START")
print(final_url)
print("GENERATED_URL_END")
