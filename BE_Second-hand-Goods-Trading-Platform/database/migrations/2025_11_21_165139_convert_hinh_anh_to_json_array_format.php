<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert tất cả hinh_anh sang format JSON array với URL đầy đủ
     */
    public function up(): void
    {
        $appUrl = config('app.url', 'http://127.0.0.1:8000');
        
        // Lấy tất cả sản phẩm có hinh_anh
        $sanPhams = DB::table('san_phams')
            ->whereNotNull('hinh_anh')
            ->where('hinh_anh', '!=', '')
            ->get(['id', 'hinh_anh']);

        foreach ($sanPhams as $sanPham) {
            $hinhAnh = $sanPham->hinh_anh;
            $converted = $this->convertHinhAnh($hinhAnh, $appUrl);
            
            if ($converted !== null) {
                DB::table('san_phams')
                    ->where('id', $sanPham->id)
                    ->update(['hinh_anh' => $converted]);
            }
        }
    }

    /**
     * Convert hinh_anh sang format JSON array với URL đầy đủ
     * 
     * @param string $hinhAnh
     * @param string $appUrl
     * @return string|null
     */
    private function convertHinhAnh($hinhAnh, $appUrl)
    {
        if (empty($hinhAnh)) {
            return null;
        }

        $trimmed = trim($hinhAnh);
        $images = [];

        // Nếu là JSON array string
        if (strpos($trimmed, '[') === 0 && substr($trimmed, -1) === ']') {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $images = $decoded;
            }
        } else {
            // Nếu là URL string hoặc path đơn giản
            $images = [$trimmed];
        }

        // Convert mỗi image sang URL đầy đủ
        $convertedImages = [];
        foreach ($images as $image) {
            if (empty($image) || !is_string($image)) {
                continue;
            }

            $image = trim($image);
            
            // Nếu đã là full URL (http:// hoặc https://)
            if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
                $convertedImages[] = $image;
            }
            // Nếu là path bắt đầu bằng /storage/ hoặc storage/
            elseif (strpos($image, '/storage/') === 0 || strpos($image, 'storage/') === 0) {
                // Loại bỏ storage/ ở đầu nếu có
                $path = ltrim($image, '/');
                if (strpos($path, 'storage/') === 0) {
                    $path = '/' . $path;
                } else {
                    $path = '/' . $path;
                }
                $convertedImages[] = rtrim($appUrl, '/') . $path;
            }
            // Các trường hợp khác, giữ nguyên
            else {
                $convertedImages[] = $image;
            }
        }

        if (empty($convertedImages)) {
            return null;
        }

        // Trả về JSON array string không escape slashes
        return json_encode($convertedImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần rollback vì không thể biết format cũ là gì
    }
};
