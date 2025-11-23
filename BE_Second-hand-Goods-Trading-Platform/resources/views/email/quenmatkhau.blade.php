<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mã xác nhận đổi mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background-color: #fff;
            border: 1px solid #e3e3e3;
            border-radius: 8px;
            padding: 20px;
        }
        .otp {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 3px;
        }
        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xin chào,</h2>
        <p>Bạn đã yêu cầu lấy lại mật khẩu. Mã xác nhận của bạn là:</p>

        <div class="otp">{{ $ma_xac_nhan }}</div>

        <p>Vui lòng sử dụng mã này để xác minh và đổi mật khẩu mới.</p>

        <p class="footer">Nếu bạn không yêu cầu thay đổi mật khẩu, vui lòng bỏ qua email này.</p>
        <p class="footer">Trân trọng,<br>Đội ngũ hỗ trợ</p>
    </div>
</body>
</html>
