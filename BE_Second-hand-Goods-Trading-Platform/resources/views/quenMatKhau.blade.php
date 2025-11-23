<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Mật Khẩu - Tứ Trụ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #334155;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f8fafc; min-height: 100vh;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); overflow: hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e293b 0%, #475569 100%); padding: 40px 30px; text-align: center; position: relative;">
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><defs><pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><circle cx=\"50\" cy=\"50\" r=\"1\" fill=\"%23ffffff\" opacity=\"0.1\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grain)\"/></svg>'); opacity: 0.3;"></div>
                            <div style="position: relative; z-index: 1;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);">
                                    🎬 Tứ Trụ
                                </h1>
                                <p style="margin: 8px 0 0 0; color: #e2e8f0; font-size: 16px; font-weight: 400; opacity: 0.9;">
                                   Chào Mừng Bạn Đến Với Chợ Đồ Cũ
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 50px 40px 40px 40px;">
                            <!-- Security Alert -->
                            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 30px; position: relative; overflow: hidden;">
                                <div style="position: absolute; top: -10px; right: -10px; width: 40px; height: 40px; background: #f59e0b; border-radius: 50%; opacity: 0.2;"></div>
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <div style="width: 20px; height: 20px; background: #f59e0b; border-radius: 50%; margin-right: 12px; display: flex; align-items: center; justify-content: center;">
                                        <span style="color: #ffffff; font-size: 12px; font-weight: bold;">⚠</span>
                                    </div>
                                    <h3 style="margin: 0; color: #92400e; font-size: 16px; font-weight: 600;">
                                        Yêu Cầu Reset Mật Khẩu
                                    </h3>
                                </div>
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
                                    Chúng tôi đã nhận được yêu cầu reset mật khẩu cho tài khoản của bạn.
                                </p>
                            </div>

                            <h2 style="margin: 0 0 20px 0; color: #1e293b; font-size: 24px; font-weight: 600; text-align: center;">
                                Xin chào <span style="color: #f59e0b;">{{$data['ho_va_ten']}}</span>! 👋
                            </h2>

                            <p style="margin: 0 0 25px 0; color: #64748b; font-size: 16px; text-align: center; line-height: 1.6;">
                                Bạn đã yêu cầu reset mật khẩu cho tài khoản Tứ Trụ của mình.
                                Nhấn vào nút bên dưới để tạo mật khẩu mới.
                            </p>

                            <!-- Reset Button -->
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ $data['link']}}" style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 12px; font-weight: 600; font-size: 16px; letter-spacing: 0.5px; text-transform: uppercase; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3); transition: all 0.3s ease; position: relative; overflow: hidden;">
                                    <span style="position: relative; z-index: 1;">🔒 Reset Mật Khẩu</span>
                                    <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent); transition: left 0.5s ease;"></div>
                                </a>
                            </div>

                            <!-- Time Limit Warning -->
                            <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #f87171; border-radius: 12px; padding: 20px; margin: 30px 0; text-align: center;">
                                <div style="margin-bottom: 12px;">
                                    <span style="font-size: 24px;">⏰</span>
                                </div>
                                <h4 style="margin: 0 0 8px 0; color: #dc2626; font-size: 16px; font-weight: 600;">
                                    Thời Gian Có Hiệu Lực
                                </h4>
                                <p style="margin: 0; color: #dc2626; font-size: 14px; line-height: 1.5;">
                                    Link reset mật khẩu này sẽ hết hạn sau <strong>60 phút</strong> kể từ khi được gửi.<br>
                                    Vui lòng thực hiện reset trong thời gian này.
                                </p>
                            </div>

                            <!-- Alternative Link -->
                            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; margin: 30px 0;">
                                <h4 style="margin: 0 0 12px 0; color: #475569; font-size: 14px; font-weight: 600;">
                                    Nếu nút không hoạt động, copy link sau:
                                </h4>
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; word-break: break-all;">
                                    <a href="{{ $data['link']}}" style="color: #3b82f6; text-decoration: none; font-size: 14px; font-family: 'Courier New', monospace;">
                                        {{$data['link']}}
                                    </a>
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #3b82f6; border-radius: 12px; padding: 20px; margin: 30px 0;">
                                <div style="display: flex; align-items: flex-start;">
                                    <div style="width: 20px; height: 20px; background: #3b82f6; border-radius: 50%; margin-right: 15px; margin-top: 2px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <span style="color: #ffffff; font-size: 12px; font-weight: bold;">🛡</span>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0 0 8px 0; color: #1e40af; font-size: 16px; font-weight: 600;">
                                            Lưu Ý Bảo Mật
                                        </h4>
                                        <ul style="margin: 0; padding-left: 0; color: #1e40af; font-size: 14px; line-height: 1.6;">
                                            <li style="margin-bottom: 6px;">• Nếu bạn không yêu cầu reset mật khẩu, vui lòng bỏ qua email này</li>
                                            <li style="margin-bottom: 6px;">• Không chia sẻ link reset với bất kỳ ai khác</li>
                                            <li style="margin-bottom: 0;">• Tài khoản của bạn vẫn an toàn cho đến khi bạn tạo mật khẩu mới</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 1px solid #e2e8f0;">
                                <p style="margin: 0; color: #64748b; font-size: 14px;">
                                    Cần hỗ trợ? Liên hệ với chúng tôi tại:
                                    <a href="mailto:support@tutru.com" style="color: #f59e0b; text-decoration: none; font-weight: 500;">
                                        support@tutru.com
                                    </a>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 30px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <div style="margin-bottom: 20px;">
                                <h3 style="margin: 0 0 8px 0; color: #475569; font-size: 18px; font-weight: 600;">
                                    🎬 Tứ Trụ
                                </h3>
                                <p style="margin: 0; color: #64748b; font-size: 14px;">
                                    Trải nghiệm điện ảnh đẳng cấp thế giới
                                </p>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <a href="#" style="display: inline-block; margin: 0 8px; color: #64748b; text-decoration: none; font-size: 12px; padding: 8px 16px; background: #ffffff; border-radius: 6px; transition: all 0.3s ease;">
                                    Trang Chủ
                                </a>
                                <a href="#" style="display: inline-block; margin: 0 8px; color: #64748b; text-decoration: none; font-size: 12px; padding: 8px 16px; background: #ffffff; border-radius: 6px; transition: all 0.3s ease;">
                                    Liên Hệ
                                </a>
                                <a href="#" style="display: inline-block; margin: 0 8px; color: #64748b; text-decoration: none; font-size: 12px; padding: 8px 16px; background: #ffffff; border-radius: 6px; transition: all 0.3s ease;">
                                    Chính Sách
                                </a>
                            </div>

                            <div style="border-top: 1px solid #cbd5e1; padding-top: 20px;">
                                <p style="margin: 0 0 8px 0; color: #64748b; font-size: 12px;">
                                    © 2024 Tứ Trụ. Tất cả quyền được bảo lưu.
                                </p>
                                <p style="margin: 0; color: #94a3b8; font-size: 11px;">
                                    Địa chỉ: 123 Đường Điện Ảnh, Quận 1, TP.HCM | Hotline: 1900-CINEMA
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <style>
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
            }
            .email-content {
                padding: 20px !important;
            }
            .email-header {
                padding: 30px 20px !important;
            }
            .email-header h1 {
                font-size: 24px !important;
            }
            .reset-button {
                padding: 14px 30px !important;
                font-size: 14px !important;
            }
            .footer-links a {
                display: block !important;
                margin: 5px 0 !important;
            }
        }
    </style>
</body>
</html>
