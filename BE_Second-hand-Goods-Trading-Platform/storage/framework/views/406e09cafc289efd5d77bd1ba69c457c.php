<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xử lý thanh toán VNPAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .callback-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            margin: 20px;
        }
        .status-icon {
            font-size: 80px;
            text-align: center;
            margin-bottom: 20px;
        }
        .status-icon.success {
            color: #28a745;
        }
        .status-icon.failed {
            color: #dc3545;
        }
        .status-icon.processing {
            color: #ffc107;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
            word-break: break-all;
        }
        .redirect-notice {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="callback-container">
        <?php if(isset($status) && $status === 'success'): ?>
            <div class="status-icon success">
                <i class='bx bx-check-circle'></i>
            </div>
            <h2 class="text-center text-success mb-4">Thanh toán thành công!</h2>
        <?php elseif(isset($status) && $status === 'failed'): ?>
            <div class="status-icon failed">
                <i class='bx bx-x-circle'></i>
            </div>
            <h2 class="text-center text-danger mb-4">Thanh toán thất bại</h2>
        <?php else: ?>
            <div class="status-icon processing">
                <div class="spinner-border text-warning mx-auto" role="status">
                    <span class="visually-hidden">Đang xử lý...</span>
                </div>
            </div>
            <h2 class="text-center text-warning mb-4">Đang xử lý thanh toán...</h2>
        <?php endif; ?>

        <div class="info-card">
            <h5 class="mb-3"><i class='bx bx-info-circle'></i> Thông tin giao dịch</h5>
            
            <?php if(isset($order_code)): ?>
            <div class="info-row">
                <span class="info-label">Mã đơn hàng:</span>
                <span class="info-value"><strong><?php echo e($order_code); ?></strong></span>
            </div>
            <?php endif; ?>

            <?php if(isset($vnp_TransactionNo)): ?>
            <div class="info-row">
                <span class="info-label">Mã giao dịch VNPAY:</span>
                <span class="info-value"><?php echo e($vnp_TransactionNo); ?></span>
            </div>
            <?php endif; ?>

            <?php if(isset($vnp_Amount)): ?>
            <div class="info-row">
                <span class="info-label">Số tiền:</span>
                <span class="info-value"><strong><?php echo e(number_format($vnp_Amount / 100, 0, ',', '.')); ?> ₫</strong></span>
            </div>
            <?php endif; ?>

            <?php if(isset($vnp_BankCode)): ?>
            <div class="info-row">
                <span class="info-label">Ngân hàng:</span>
                <span class="info-value"><?php echo e($vnp_BankCode); ?></span>
            </div>
            <?php endif; ?>

            <?php if(isset($vnp_CardType)): ?>
            <div class="info-row">
                <span class="info-label">Loại thẻ:</span>
                <span class="info-value"><?php echo e($vnp_CardType); ?></span>
            </div>
            <?php endif; ?>

            <?php if(isset($vnp_PayDate)): ?>
            <div class="info-row">
                <span class="info-label">Thời gian thanh toán:</span>
                <span class="info-value">
                    <?php
                        // Format: YmdHis (20251118001523)
                        $date = \DateTime::createFromFormat('YmdHis', $vnp_PayDate);
                        echo $date ? $date->format('d/m/Y H:i:s') : $vnp_PayDate;
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if(isset($vnp_ResponseCode)): ?>
            <div class="info-row">
                <span class="info-label">Mã phản hồi:</span>
                <span class="info-value">
                    <span class="badge <?php echo e($vnp_ResponseCode == '00' ? 'bg-success' : 'bg-danger'); ?>">
                        <?php echo e($vnp_ResponseCode); ?>

                    </span>
                </span>
            </div>
            <?php endif; ?>

            <?php if(isset($message)): ?>
            <div class="info-row">
                <span class="info-label">Thông báo:</span>
                <span class="info-value"><?php echo e($message); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if(isset($redirect_url)): ?>
        <div class="redirect-notice">
            <p class="mb-2"><i class='bx bx-time'></i> <strong>Đang chuyển hướng...</strong></p>
            <p class="mb-0 small text-muted">Bạn sẽ được chuyển đến trang kết quả trong <span id="countdown">5</span> giây.</p>
            <div class="mt-3">
                <a href="<?php echo e($redirect_url); ?>" class="btn btn-primary">
                    <i class='bx bx-right-arrow-alt'></i> Chuyển ngay
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if(isset($debug) && config('app.debug')): ?>
        <div class="info-card mt-3">
            <h6 class="text-muted">Debug Info (chỉ hiển thị khi APP_DEBUG=true)</h6>
            <pre style="font-size: 12px; max-height: 200px; overflow-y: auto;"><?php echo e(json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <script>
        <?php if(isset($redirect_url)): ?>
        (function() {
            let countdown = 5;
            const countdownEl = document.getElementById('countdown');
            
            const timer = setInterval(function() {
                countdown--;
                if (countdownEl) {
                    countdownEl.textContent = countdown;
                }
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = '<?php echo e($redirect_url); ?>';
                }
            }, 1000);
        })();
        <?php endif; ?>
    </script>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\Shopee - Copy\BE_Second-hand-Goods-Trading-Platform\resources\views/vnpay_callback.blade.php ENDPATH**/ ?>