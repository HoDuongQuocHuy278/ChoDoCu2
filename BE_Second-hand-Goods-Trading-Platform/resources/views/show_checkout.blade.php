<!-- resources/views/show_checkout.blade.php -->

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán VNPAY</title>
</head>
<body>
    <h2>Trang thanh toán</h2>
    <div action="{{ url('/vnpay_payment') }}" method="POST">
        @csrf
        <input type="hidden" name="total_vnpay" value="{{ $total_after }}">
        <button type="submit" name="redirect">Thanh toán VNPAY</button>
    </div>
</body>
</html>
