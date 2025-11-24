<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SanPham extends Model
{
    protected $table = 'san_phams';

    protected $fillable = [
        'ten_san_pham',
        'mo_ta',
        'gia',
        'hinh_anh',
        'tinh_trang',
        'category',
        'danh_muc_id',
        'thuong_hieu',
        'mau_sac',
        'kich_thuoc',
        'dia_chi',
        'tinh_thanh',
        'quan_huyen',
        'khach_hang_id',
        'trang_thai',
        'luot_xem',
    ];

    protected $casts = [
        'gia' => 'decimal:2',
        'luot_xem' => 'integer',
        'trang_thai' => 'integer',
        // hinh_anh là string đơn giản giống danh_mucs, không có accessor
    ];

    /**
     * Get the customer that owns the product.
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /**
     * Get the category that owns the product.
     */
    public function danhMuc(): BelongsTo
    {
        return $this->belongsTo(DanhMuc::class, 'danh_muc_id');
    }

    /**
     * Get the comments for the product.
     */
    public function binhLuans(): HasMany
    {
        return $this->hasMany(BinhLuan::class, 'san_pham_id');
    }

    /**
     * Get the ratings for the product.
     */
    public function danhGias(): HasMany
    {
        return $this->hasMany(DanhGia::class, 'san_pham_id');
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Scope for sold products
     */
    public function scopeSold($query)
    {
        return $query->where('trang_thai', 2);
    }

    /**
     * Scope for hidden products
     */
    public function scopeHidden($query)
    {
        return $query->where('trang_thai', 3);
    }

    /**
     * Normalize hinh_anh input - nhận cả URL string và JSON array string
     * Chuyển đổi về dạng JSON array string để lưu vào database
     * 
     * @param mixed $value - Có thể là: string URL, JSON array string, hoặc array
     * @return string|null - JSON array string hoặc null
     */
    public static function normalizeHinhAnh($value)
    {
        if (empty($value)) {
            return null;
        }

        // Nếu là array, chuyển thành JSON string
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        // Nếu là string, kiểm tra xem có phải JSON array không
        if (is_string($value)) {
            $trimmed = trim($value);
            
            // Nếu bắt đầu bằng [ và kết thúc bằng ], có thể là JSON array
            if (strpos($trimmed, '[') === 0 && substr($trimmed, -1) === ']') {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Đã là JSON array hợp lệ, trả về normalized
                    return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
            
            // Nếu là URL đơn giản (bắt đầu bằng http:// hoặc https://)
            if (strpos($trimmed, 'http://') === 0 || strpos($trimmed, 'https://') === 0) {
                // Chuyển thành array với 1 phần tử
                return json_encode([$trimmed], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            
            // Nếu là path (bắt đầu bằng /storage/ hoặc storage/)
            // Giữ nguyên relative path để tương thích khi chia sẻ qua mạng
            if (strpos($trimmed, '/storage/') === 0 || strpos($trimmed, 'storage/') === 0) {
                // Đảm bảo path bắt đầu bằng /
                $path = strpos($trimmed, '/') === 0 ? $trimmed : '/' . $trimmed;
                // Chuyển thành array với 1 phần tử (giữ relative path)
                return json_encode([$path], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            
            // Trường hợp khác, giữ nguyên
            return $trimmed;
        }

        return null;
    }

    /**
     * Convert relative path thành absolute URL dựa trên request hiện tại
     * Giúp hình ảnh hiển thị đúng khi chia sẻ qua mạng
     * Ưu tiên sử dụng IP mạng thay vì localhost
     * 
     * @param string $path - Relative path (e.g., '/storage/products/image.jpg')
     * @return string - Absolute URL
     */
    private function convertPathToUrl($path)
    {
        // Nếu đã là absolute URL (http:// hoặc https://), trả về nguyên
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        
        // Nếu là relative path, convert thành absolute URL dựa trên request hiện tại
        if (strpos($path, '/') === 0) {
            // Lấy scheme và host từ request hiện tại
            $request = request();
            if ($request) {
                $scheme = $request->getScheme();
                $host = $request->getHost();
                $port = $request->getPort();
                
                // Nếu host là localhost hoặc 127.0.0.1, thử lấy IP mạng từ config
                if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
                    $appUrl = config('app.url', 'http://127.0.0.1:8000');
                    // Nếu APP_URL có IP mạng, dùng nó
                    if (preg_match('/http[s]?:\/\/(\d+\.\d+\.\d+\.\d+)/', $appUrl, $matches)) {
                        $host = $matches[1];
                    }
                }
                
                // Thêm port nếu không phải port mặc định
                $baseUrl = $scheme . '://' . $host;
                if ($port && $port != 80 && $port != 443) {
                    $baseUrl .= ':' . $port;
                }
                
                return $baseUrl . $path;
            }
        }
        
        // Fallback: dùng config APP_URL
        $appUrl = config('app.url', 'http://127.0.0.1:8000');
        return rtrim($appUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Lấy danh sách ảnh dưới dạng array
     * Tự động convert relative paths thành absolute URLs
     * 
     * @return array
     */
    public function getImagesArray()
    {
        if (empty($this->hinh_anh)) {
            return [];
        }

        $images = [];

        // Nếu là string, thử decode JSON
        if (is_string($this->hinh_anh)) {
            $trimmed = trim($this->hinh_anh);
            
            // Nếu bắt đầu bằng [ và kết thúc bằng ], thử decode
            if (strpos($trimmed, '[') === 0 && substr($trimmed, -1) === ']') {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $images = array_filter($decoded, function($item) {
                        return !empty($item) && is_string($item);
                    });
                }
            } else {
                // Nếu là URL hoặc path đơn giản
                if (!empty($trimmed)) {
                    $images = [$trimmed];
                }
            }
        } elseif (is_array($this->hinh_anh)) {
            // Nếu là array (không nên xảy ra vì database lưu string)
            $images = array_filter($this->hinh_anh, function($item) {
                return !empty($item) && is_string($item);
            });
        }

        // Convert tất cả relative paths thành absolute URLs
        return array_map(function($image) {
            return $this->convertPathToUrl($image);
        }, $images);
    }

    /**
     * Lấy ảnh đầu tiên
     * 
     * @return string|null
     */
    public function getFirstImage()
    {
        $images = $this->getImagesArray();
        return !empty($images) ? $images[0] : null;
    }

    /**
     * Setter cho hinh_anh - tự động normalize
     */
    public function setHinhAnhAttribute($value)
    {
        $this->attributes['hinh_anh'] = self::normalizeHinhAnh($value);
    }
}
