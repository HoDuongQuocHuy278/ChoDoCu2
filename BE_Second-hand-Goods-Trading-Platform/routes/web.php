<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/checkout', function () {
    $total_after = 200000; // Bạn có thể lấy từ giỏ hàng hoặc tính động
    return view('show_checkout', ['total_after' => $total_after]);
});

// Route xử lý POST tới VNPAY
use App\Http\Controllers\ThanhToanController;

Route::post('/vnpay_payment', [ThanhToanController::class, 'vnpay_payment']);

// Callback từ VNPAY (phải là GET và không cần auth)
// Route này phải match chính xác với URL mà VNPAY gọi
Route::get('/api/client/payment/vnpay/callback', [ThanhToanController::class, 'vnpayCallback'])->name('vnpay.callback');

// Route để serve ảnh từ storage - ĐẶT TRƯỚC TẤT CẢ CÁC ROUTE KHÁC
// Route này sẽ serve file trực tiếp từ storage, không qua middleware
Route::get('/storage/{path}', function ($path) {
    try {
        // Bảo vệ khỏi path traversal
        $path = str_replace(['..', '\\'], '', $path);
        $path = ltrim($path, '/');

        if (empty($path)) {
            return response('File not found', 404);
        }

        // Thử nhiều đường dẫn
        $filePath = storage_path('app/public/' . $path);
        
        // Nếu không tìm thấy, thử qua symlink
        if (!file_exists($filePath) || !is_file($filePath)) {
            $symlinkPath = public_path('storage/' . $path);
            if (file_exists($symlinkPath) && is_file($symlinkPath)) {
                $filePath = $symlinkPath;
            }
        }

        // Kiểm tra file tồn tại
        if (!file_exists($filePath) || !is_file($filePath)) {
            // Trả về 404 với thông báo rõ ràng
            \Log::warning('Storage file not found', [
                'requested_path' => $path,
                'storage_path' => storage_path('app/public/' . $path),
                'symlink_path' => public_path('storage/' . $path),
                'storage_exists' => file_exists(storage_path('app/public/' . $path)),
                'symlink_exists' => file_exists(public_path('storage/' . $path)),
            ]);
            return response('File not found: ' . $path, 404)
                ->header('Content-Type', 'text/plain');
        }

        // Kiểm tra file có thể đọc được
        if (!is_readable($filePath)) {
            return response('File not readable', 403);
        }

        // Đọc file
        $file = file_get_contents($filePath);

        // Xác định mime type
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Trả về file với headers
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Content-Length', filesize($filePath));

    } catch (\Exception $e) {
        \Log::error('Storage serve error: ' . $e->getMessage(), [
            'path' => $path ?? 'unknown',
            'trace' => $e->getTraceAsString(),
        ]);
        return response('Error: ' . $e->getMessage(), 500);
    }
})->where('path', '.*')->name('storage.serve');

// Test route để kiểm tra storage files và ảnh
Route::get('/test-images', function () {
    $productsDir = storage_path('app/public/products');
    $files = [];

    // Lấy danh sách file ảnh trong thư mục products
    if (is_dir($productsDir)) {
        $allFiles = scandir($productsDir);
        foreach ($allFiles as $file) {
            if ($file !== '.' && $file !== '..' && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $filePath = 'products/' . $file;
                $fullPath = storage_path('app/public/' . $filePath);

                $files[] = [
                    'filename' => $file,
                    'path' => $filePath,
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath),
                    'readable' => file_exists($fullPath) ? is_readable($fullPath) : false,
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                    'url' => url('/storage/' . $filePath),
                ];
            }
        }
    }

    // Lấy sản phẩm từ database để test
    $sanPhams = \App\Models\SanPham::select('id', 'ten_san_pham', 'hinh_anh')
        ->whereNotNull('hinh_anh')
        ->limit(5)
        ->get()
        ->map(function ($sp) {
            return [
                'id' => $sp->id,
                'ten_san_pham' => $sp->ten_san_pham,
                'hinh_anh' => $sp->hinh_anh,
                'hinh_anh_url' => url('/storage/' . str_replace(url('/storage/'), '', $sp->hinh_anh)),
            ];
        });

    // Test file cụ thể
    $testFile = 'products/1763477118_691c867ec9c5d.png';
    $testFilePath = storage_path('app/public/' . $testFile);

    return response()->json([
        'app_url' => config('app.url'),
        'storage_info' => [
            'products_dir' => $productsDir,
            'products_dir_exists' => is_dir($productsDir),
            'public_storage_link' => public_path('storage'),
            'storage_link_exists' => is_link(public_path('storage')) || is_dir(public_path('storage')),
        ],
        'test_file' => [
            'path' => $testFile,
            'full_path' => $testFilePath,
            'exists' => file_exists($testFilePath),
            'readable' => file_exists($testFilePath) ? is_readable($testFilePath) : false,
            'size' => file_exists($testFilePath) ? filesize($testFilePath) : 0,
            'url' => url($testFile),
            'test_direct' => 'http://127.0.0.1:8000/' . $testFile,
        ],
        'files_in_storage' => $files,
        'products_from_db' => $sanPhams,
        'test_instructions' => [
            '1' => 'Kiểm tra test_file để xem file cụ thể có tồn tại và readable không',
            '2' => 'Kiểm tra files_in_storage để xem danh sách file ảnh',
            '3' => 'Mở URL trong test_file.url hoặc test_direct để xem ảnh có hiển thị không',
            '4' => 'Kiểm tra products_from_db để xem URL ảnh từ database',
        ],
    ]);
});
