<!DOCTYPE html>
<html lang="vi">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Đăng ký thành công</title>
		<style>
			/* Basic responsive email styles */
			body { background-color: #f4f6f8; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif; }
			.email-wrapper { width: 100%; background-color: #f4f6f8; padding: 20px 0; }
			.email-content { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
			.email-header { background: linear-gradient(90deg,#6c5ce7,#00b894); color: white; padding: 28px 32px; text-align: center; }
			.email-body { padding: 28px 32px; color: #1f2937; }
			.greeting { font-size: 18px; margin-bottom: 12px; }
			.message { font-size: 15px; line-height: 1.5; margin-bottom: 20px; }
			.btn { display: inline-block; background: #6c5ce7; color: white; text-decoration: none; padding: 12px 18px; border-radius: 6px; font-weight: 600; }
			.meta { font-size: 13px; color: #6b7280; margin-top: 18px; }
			.footer { text-align: center; padding: 18px 16px; font-size: 12px; color: #9ca3af; }
			.small { font-size: 13px; color: #6b7280; }
			@media only screen and (max-width: 480px) {
				.email-header { padding: 20px 16px; }
				.email-body { padding: 20px 16px; }
			}
		</style>
	</head>
	<body>
		<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td align="center">
					<table class="email-content" width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td class="email-header">
								<h1 style="margin:0; font-size:22px;">Chào mừng đến với StreamMart</h1>
								<p style="margin:6px 0 0; opacity:0.95;">Tài khoản của bạn đã được tạo thành công</p>
							</td>
						</tr>
						<tr>
							<td class="email-body">
								<p class="greeting">Xin chào {{ $data->ho_ten }},</p>

								<p class="message">Cảm ơn bạn đã đăng ký tài khoản tại <strong>StreamMart</strong>. Tài khoản của bạn đã được tạo thành công và bạn có thể bắt đầu khám phá nền tảng ngay bây giờ.</p>

								<p class="small">Một số mẹo bắt đầu:</p>
								<ul style="color:#374151;">
									<li>Hoàn thiện hồ sơ cá nhân của bạn.</li>
									<li>Khám phá nội dung được đề xuất cho bạn.</li>
									<li>Liên hệ đội ngũ hỗ trợ nếu cần trợ giúp.</li>
								</ul>

								<p class="small">Nếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email hoặc liên hệ với chúng tôi.</p>
							</td>
						</tr>
						<tr>
							<td class="footer">
								<p style="margin:0 0 8px 0;">StreamMart • Hỗ trợ: <a href="mailto:help@example.com">help@example.com</a></p>
								<p style="margin:0;">&copy; {{ date('Y') }} StreamMart. Bảo lưu mọi quyền.</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>
