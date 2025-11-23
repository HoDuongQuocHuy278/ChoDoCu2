<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Normalize lại tất cả hinh_anh để đảm bảo không có escape slashes
     */
    public function up(): void
    {
        // Lấy tất cả sản phẩm có hinh_anh
        $sanPhams = DB::table('san_phams')
            ->whereNotNull('hinh_anh')
            ->where('hinh_anh', '!=', '')
            ->get(['id', 'hinh_anh']);

        foreach ($sanPhams as $sanPham) {
            $hinhAnh = $sanPham->hinh_anh;
            $normalized = $this->normalizeHinhAnh($hinhAnh);
            
            if ($normalized !== null && $normalized !== $hinhAnh) {
                DB::table('san_phams')
                    ->where('id', $sanPham->id)
                    ->update(['hinh_anh' => $normalized]);
            }
        }
    }

    /**
     * Normalize hinh_anh - đảm bảo không escape slashes
     */
    private function normalizeHinhAnh($value)
    {
        if (empty($value)) {
            return null;
        }

        $trimmed = trim($value);
        
        // Nếu là JSON array string, parse và re-encode
        if (strpos($trimmed, '[') === 0 && substr($trimmed, -1) === ']') {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Fix từng URL trong array
                $fixed = [];
                foreach ($decoded as $url) {
                    if (is_string($url)) {
                        // Nếu URL có /products/ nhưng không có /storage/products/, sửa lại
                        if (strpos($url, '/products/') !== false && strpos($url, '/storage/products/') === false) {
                            $url = str_replace('/products/', '/storage/products/', $url);
                        }
                        // Nếu URL có IP khác, thay bằng APP_URL
                        $appUrl = config('app.url', 'http://127.0.0.1:8000');
                        if (preg_match('#^https?://[^/]+#', $url, $matches)) {
                            $currentBase = $matches[0];
                            if ($currentBase !== $appUrl) {
                                $url = str_replace($currentBase, $appUrl, $url);
                            }
                        }
                        $fixed[] = $url;
                    }
                }
                // Re-encode với JSON_UNESCAPED_SLASHES
                return json_encode($fixed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        
        // Nếu là URL string đơn giản, wrap vào array
        if (strpos($trimmed, 'http://') === 0 || strpos($trimmed, 'https://') === 0) {
            return json_encode([$trimmed], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        
        // Nếu là path, convert thành full URL và wrap vào array
        if (strpos($trimmed, '/storage/') === 0 || strpos($trimmed, 'storage/') === 0 || strpos($trimmed, '/products/') === 0 || strpos($trimmed, 'products/') === 0) {
            $appUrl = config('app.url', 'http://127.0.0.1:8000');
            
            // Nếu là /products/ hoặc products/, thêm /storage/ vào
            if (strpos($trimmed, '/products/') === 0) {
                $path = '/storage' . $trimmed;
            } elseif (strpos($trimmed, 'products/') === 0) {
                $path = '/storage/' . $trimmed;
            } else {
                $path = ltrim($trimmed, '/');
                if (strpos($path, 'storage/') !== 0) {
                    $path = 'storage/' . $path;
                }
                $path = '/' . $path;
            }
            
            $fullUrl = rtrim($appUrl, '/') . $path;
            return json_encode([$fullUrl], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        
        // Nếu là URL có /products/ nhưng không có /storage/, sửa lại
        if (strpos($trimmed, 'http://') === 0 || strpos($trimmed, 'https://') === 0) {
            // Kiểm tra xem có /products/ nhưng không có /storage/products/
            if (strpos($trimmed, '/products/') !== false && strpos($trimmed, '/storage/products/') === false) {
                $trimmed = str_replace('/products/', '/storage/products/', $trimmed);
            }
            return json_encode([$trimmed], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        
        return $trimmed;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần rollback
    }
};
