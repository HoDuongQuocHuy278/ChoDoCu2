<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Minh Đăng Ký - Tứ Trụ</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 20px 0;">
                <table role="presentation"
                    style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <div
                                style="background-color: #ffffff; display: inline-block; padding: 15px 25px; border-radius: 8px; margin-bottom: 20px;">
                                <h1
                                    style="margin: 0; color: #667eea; font-size: 28px; font-weight: bold; letter-spacing: 1px;">
                                    🎬 Tứ Trụ
                                </h1>
                            </div>
                            <h2 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 300;">
                                Chào mừng bạn đến với Tứ Trụ!
                            </h2>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <div style="text-align: center; margin-bottom: 30px;">
                                <h3 style="color: #333333; font-size: 22px; margin-bottom: 20px; font-weight: 600;">
                                    Xác Minh Tài Khoản Của Bạn
                                </h3>
                                <p style="color: #666666; font-size: 16px; margin-bottom: 25px; line-height: 1.8;">
                                    Cảm ơn {{ $data['ho_va_ten'] }} đã đăng ký tài khoản tại <strong>Tứ Trụ</strong>!
                                    Để hoàn tất quá trình đăng ký và bắt đầu trải nghiệm những bộ phim tuyệt vời,
                                    vui lòng nhấp vào nút bên dưới để xác minh địa chỉ email của bạn.
                                </p>
                            </div>

                            <!-- Verification Button -->
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="{{ $data['link'] }}"
                                    style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; font-size: 18px; font-weight: 600; border-radius: 50px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px;">
                                    ✉️ Xác Minh Email
                                </a>
                            </div>

                            <!-- Alternative Link -->
                            <div
                                style="background-color: #f8f9fa; padding: 25px; border-radius: 8px; margin: 30px 0; border-left: 4px solid #667eea;">
                                <p style="color: #666666; font-size: 14px; margin-bottom: 15px; font-weight: 600;">
                                    Nếu nút không hoạt động, bạn có thể copy và paste đường link sau vào trình duyệt:
                                </p>
                                <p
                                    style="word-break: break-all; background-color: #ffffff; padding: 12px; border-radius: 4px; border: 1px solid #e9ecef; font-family: monospace; font-size: 13px; color: #495057; margin: 0;">
                                    {{ $data['link'] }}
                                </p>
                            </div>

                            <!-- Security Notice -->
                            <div
                                style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0;">
                                <div style="display: flex; align-items: flex-start;">
                                    <span style="color: #856404; font-size: 18px; margin-right: 10px;">⚠️</span>
                                    <div>
                                        <p style="color: #856404; font-size: 14px; margin: 0; font-weight: 600;">
                                            Lưu ý bảo mật:
                                        </p>
                                        <p
                                            style="color: #856404; font-size: 13px; margin: 5px 0 0 0; line-height: 1.5;">
                                            Link xác minh này chỉ có hiệu lực trong 24 giờ. Nếu bạn không thực hiện đăng
                                            ký này, vui lòng bỏ qua email.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Features Section -->
                    <tr>
                        <td style="padding: 0 30px 40px 30px;">
                            <div
                                style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; padding: 25px; text-align: center;">
                                <h4 style="color: #ffffff; font-size: 18px; margin-bottom: 15px; font-weight: 600;">
                                    🎉 Những gì đang chờ bạn tại Tứ Trụ:
                                </h4>
                                <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 15px;">
                                    <div style="flex: 1; min-width: 150px;">
                                        <div style="color: #ffffff; font-size: 24px; margin-bottom: 8px;">🎬</div>
                                        <p style="color: #ffffff; font-size: 13px; margin: 0; opacity: 0.9;">Phim mới
                                            nhất</p>
                                    </div>
                                    <div style="flex: 1; min-width: 150px;">
                                        <div style="color: #ffffff; font-size: 24px; margin-bottom: 8px;">🎟️</div>
                                        <p style="color: #ffffff; font-size: 13px; margin: 0; opacity: 0.9;">Đặt vé
                                            online</p>
                                    </div>
                                    <div style="flex: 1; min-width: 150px;">
                                        <div style="color: #ffffff; font-size: 24px; margin-bottom: 8px;">💰</div>
                                        <p style="color: #ffffff; font-size: 13px; margin: 0; opacity: 0.9;">Ưu đãi đặc
                                            biệt</p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #2c3e50; padding: 30px; text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <h3 style="color: #ffffff; font-size: 20px; margin-bottom: 15px; font-weight: 600;">
                                    🎬 Tứ Trụ
                                </h3>
                                <p style="color: #bdc3c7; font-size: 14px; margin: 0; line-height: 1.6;">
                                    Hệ thống chợ đồ cũ | Trải nghiệm điện ảnh đỉnh cao
                                </p>
                            </div>

                            <div style="border-top: 1px solid #34495e; padding-top: 20px; margin-top: 20px;">
                                <p style="color: #95a5a6; font-size: 12px; margin: 0 0 10px 0;">
                                    📧 Email này được gửi tự động, vui lòng không reply.
                                </p>
                                <p style="color: #95a5a6; font-size: 12px; margin: 0;">
                                    © {{ date('Y') }} Tứ Trụ. All rights reserved.
                                </p>
                                <div style="margin-top: 15px;">
                                    <a href="#"
                                        style="color: #3498db; text-decoration: none; font-size: 12px; margin: 0 10px;">Chính
                                        sách bảo mật</a>
                                    <span style="color: #7f8c8d;">|</span>
                                    <a href="#"
                                        style="color: #3498db; text-decoration: none; font-size: 12px; margin: 0 10px;">Điều
                                        khoản sử dụng</a>
                                    <span style="color: #7f8c8d;">|</span>
                                    <a href="#"
                                        style="color: #3498db; text-decoration: none; font-size: 12px; margin: 0 10px;">Liên
                                        hệ</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
