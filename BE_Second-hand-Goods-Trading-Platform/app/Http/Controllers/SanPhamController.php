<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSanPhamRequest;
use App\Http\Requests\UpdateSanPhamRequest;
use App\Http\Resources\SanPhamResource;
use App\Models\SanPham;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SanPhamController extends Controller
{
    /**
     * Danh sách sản phẩm (CLIENT)
     * Query:
     *  - page, per_page (<= 100)
     *  - category=string|all
     *  - has_discount=1
     *  - q=keyword (tên/mô tả)
     *  - price_min, price_max (VND)
     *  - rating_min (0..5)
     *  - sort = price_asc|price_desc|sold_desc|newest|discount_desc
     */
    public function index(Request $req)
    {
        $perPage = min((int) $req->get('per_page', 20), 100);

        $q = SanPham::query()->where('trang_thai', 1); // 1: đang bán

        if ($req->filled('category') && $req->category !== 'all') {
            // Tìm danh mục theo slug trước, nếu không có thì dùng category field
            $danhMuc = \App\Models\DanhMuc::where('slug', $req->category)->first();
            if ($danhMuc) {
                $q->where('danh_muc_id', $danhMuc->id);
            } else {
                $q->where('category', $req->category);
            }
        }

        if ($req->filled('q')) {
            $kw = '%' . trim($req->q) . '%';
            $q->where(function ($k) use ($kw) {
                $k->where('ten_san_pham', 'like', $kw)
                  ->orWhere('mo_ta', 'like', $kw);
            });
        }

        if ($req->filled('price_min')) {
            $q->where('gia', '>=', (int) $req->price_min);
        }
        if ($req->filled('price_max')) {
            $q->where('gia', '<=', (int) $req->price_max);
        }

        // Filter theo người bán (seller_id hoặc khach_hang_id)
        if ($req->filled('seller_id')) {
            $q->where('khach_hang_id', (int) $req->seller_id);
        }
        if ($req->filled('khach_hang_id')) {
            $q->where('khach_hang_id', (int) $req->khach_hang_id);
        }

        switch ($req->get('sort')) {
            case 'price_asc':
                $q->orderBy('gia', 'asc');
                break;
            case 'price_desc':
                $q->orderBy('gia', 'desc');
                break;
            case 'newest':
            default:
                $q->orderByDesc('id');
        }

        $paginated = $q->paginate($perPage);

        // Format products cho frontend
        $formattedData = $paginated->getCollection()->map(function ($sanPham) {
            $images = $sanPham->getImagesArray();
            return [
                'id' => $sanPham->id,
                'name' => $sanPham->ten_san_pham,
                'description' => $sanPham->mo_ta,
                'price' => (float) $sanPham->gia,
                'discount' => 0,
                'category' => $sanPham->category,
                'image' => !empty($images) ? $images[0] : null,
                'images' => $images,
                'condition' => $sanPham->tinh_trang,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $formattedData,
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Chi tiết sản phẩm (CLIENT)
     */
    public function show(SanPham $san_pham)
    {
        // Tăng lượt xem
        $san_pham->increment('luot_xem');

        // Load quan hệ
        $san_pham->load(['khachHang', 'danhMuc']);

        // Format response cho frontend
        $images = $san_pham->getImagesArray();
        $response = [
            'id' => $san_pham->id,
            'name' => $san_pham->ten_san_pham,
            'description' => $san_pham->mo_ta,
            'price' => (float) $san_pham->gia,
            'discount' => 0, // Có thể thêm sau
            'category' => $san_pham->danhMuc ? [
                'id' => $san_pham->danhMuc->id,
                'name' => $san_pham->danhMuc->ten_danh_muc,
                'slug' => $san_pham->danhMuc->slug,
            ] : ($san_pham->category ?? null),
            'image' => !empty($images) ? $images[0] : null,
            'images' => $images,
            'condition' => $san_pham->tinh_trang,
            'conditionLabel' => $this->getConditionLabel($san_pham->tinh_trang),
            'location' => $san_pham->tinh_thanh ?? $san_pham->dia_chi,
            'dia_chi' => $san_pham->dia_chi,
            'seller' => $san_pham->khachHang ? [
                'id' => $san_pham->khachHang->id,
                'name' => $san_pham->khachHang->ho_va_ten,
                'email' => $san_pham->khachHang->email,
            ] : null,
            'khach_hang_id' => $san_pham->khach_hang_id, // Thêm field này để frontend có thể check
            'seller_id' => $san_pham->khach_hang_id, // Alias cho seller_id
            'quantity' => 1, // Có thể thêm field quantity sau
            'stock' => 1,
            'slug' => $san_pham->id,
        ];

        return response()->json([
            'status' => true,
            'data' => $response,
        ]);
    }

    private function getConditionLabel($tinhTrang)
    {
        $map = [
            'moi' => 'Mới 100%',
            'cu' => 'Tốt',
            'rat_cu' => 'Khá',
        ];
        return $map[$tinhTrang] ?? 'Đang cập nhật';
    }

    /**
     * Sản phẩm tương tự (CLIENT)
     * - Theo category + gần giá nhất
     * Query: limit (mặc định 6)
     */
    public function similar(SanPham $san_pham, Request $req)
    {
        $limit = min((int) $req->get('limit', 6), 20);

        $basePrice = (float) $san_pham->gia;

        $q = SanPham::query()
            ->where('trang_thai', 1)
            ->where('category', $san_pham->category)
            ->where('id', '!=', $san_pham->id)
            ->select('*')
            ->selectRaw('ABS(gia - ?) as price_diff', [$basePrice])
            ->orderBy('price_diff', 'asc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        // Format products cho frontend
        $formattedData = $q->map(function ($item) {
            $images = $item->getImagesArray();
            return [
                'id' => $item->id,
                'name' => $item->ten_san_pham,
                'description' => $item->mo_ta,
                'price' => (float) $item->gia,
                'discount' => 0,
                'category' => $item->category,
                'image' => !empty($images) ? $images[0] : null,
                'images' => $images,
                'condition' => $item->tinh_trang,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedData,
        ]);
    }

    /**
     * Danh sách (ADMIN) – xem cả is_active=false
     * Query: same as index + is_active=0|1|all (default: all)
     */
    public function adminIndex(Request $req)
    {
        $perPage = min((int) $req->get('per_page', 20), 100);
        $q = SanPham::query();

        if ($req->filled('trang_thai') && $req->trang_thai !== 'all') {
            $q->where('trang_thai', (int) $req->trang_thai);
        }

        // Tái sử dụng filter như index
        if ($req->filled('category') && $req->category !== 'all') {
            $q->where('category', $req->category);
        }
        if ($req->filled('q')) {
            $kw = '%' . trim($req->q) . '%';
            $q->where(function ($k) use ($kw) {
                $k->where('ten_san_pham', 'like', $kw)
                    ->orWhere('mo_ta', 'like', $kw);
            });
        }
        if ($req->filled('price_min')) $q->where('gia', '>=', (int) $req->price_min);
        if ($req->filled('price_max')) $q->where('gia', '<=', (int) $req->price_max);

        switch ($req->get('sort')) {
            case 'price_asc':
                $q->orderBy('gia', 'asc'); break;
            case 'price_desc':
                $q->orderBy('gia', 'desc'); break;
            case 'newest':
            default:
                $q->orderByDesc('id');
        }

        return response()->json([
            'status' => true,
            'data' => $q->paginate($perPage),
        ]);
    }

    /**
     * Tạo mới (ADMIN)
     * - Hỗ trợ upload ảnh: field "image_file" (multipart) hoặc "image" (URL).
     */
    public function store(StoreSanPhamRequest $req)
    {
        $data = $req->validated();

        // Map từ request sang database fields
        $sanPhamData = [
            'ten_san_pham' => $data['name'] ?? '',
            'mo_ta' => $data['description'] ?? null,
            'gia' => $data['price'] ?? 0,
            'category' => $data['category'] ?? null,
            'hinh_anh' => SanPham::normalizeHinhAnh($data['hinh_anh'] ?? null),
            'trang_thai' => $data['is_active'] ?? true ? 1 : 3,
        ];

        $sp = SanPham::create($sanPhamData);

        return response()->json([
            'status' => true,
            'message' => 'Tạo sản phẩm thành công',
            'data' => $sp,
        ]);
    }

    /**
     * Cập nhật (ADMIN)
     * - Replace ảnh: nếu có image_file mới -> xóa ảnh cũ lưu ở public (nếu là local link)
     */
    public function update(UpdateSanPhamRequest $req, SanPham $san_pham)
    {
        $data = $req->validated();

        // Map từ request sang database fields
        $sanPhamData = [];
        if (isset($data['name'])) {
            $sanPhamData['ten_san_pham'] = trim($data['name']);
        }
        if (isset($data['description'])) {
            $sanPhamData['mo_ta'] = $data['description'];
        }
        if (isset($data['price'])) {
            $sanPhamData['gia'] = $data['price'];
        }
        if (isset($data['category'])) {
            $sanPhamData['category'] = $data['category'];
        }
        if (isset($data['is_active'])) {
            $sanPhamData['trang_thai'] = $data['is_active'] ? 1 : 3;
        }
        $san_pham->update($sanPhamData);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật sản phẩm thành công',
            'data' => $san_pham->fresh(),
        ]);
    }



    /**
     * Bật/Tắt hiển thị (ADMIN)
     */
    public function toggleActive(SanPham $san_pham)
    {
        // Chuyển đổi trạng thái: 1 (đang bán) <-> 3 (đã ẩn)
        if ($san_pham->trang_thai == 1) {
            $san_pham->trang_thai = 3; // Ẩn
        } else {
            $san_pham->trang_thai = 1; // Hiển thị
        }
        $san_pham->save();

        return response()->json([
            'status'  => true,
            'message' => 'Đã cập nhật trạng thái',
            'data'    => $san_pham,
        ]);
    }

    // ===== Helpers =====



    /**
     * Lấy danh sách category từ sản phẩm (deprecated - nên dùng DanhMucController)
     */
    public function danhMuc()
    {
        $categories = SanPham::query()
            ->where('trang_thai', 1)
            ->select('category')
            ->distinct()
            ->get()
            ->pluck('category');

        return response()->json([
            'status' => true,
            'data'   => $categories,
        ]);
    }

    /**
     * Đăng bán sản phẩm (CLIENT)
     * Xử lý form đăng bán từ frontend với multiple images
     */
    public function storeClient(Request $req)
    {
        try {
            // Kiểm tra đăng nhập
            $user = Auth::guard('sanctum')->user();
            if (!$user || !($user instanceof KhachHang)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vui lòng đăng nhập để đăng bán sản phẩm',
                ], 401);
            }

            // Kiểm tra user đã đăng ký bán hàng chưa
            if ($user->is_seller != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn chưa đăng ký bán hàng. Vui lòng đăng ký bán hàng trước khi đăng sản phẩm.',
                    'redirect' => '/dang-ky-ban',
                ], 403);
            }

            // Convert negotiable từ string sang boolean trước khi validate
            if ($req->has('negotiable')) {
                $negotiableValue = $req->input('negotiable');
                if (in_array($negotiableValue, ['1', 'true', 'on', 'yes'], true) || $negotiableValue === true) {
                    $req->merge(['negotiable' => true]);
                } else {
                    $req->merge(['negotiable' => false]);
                }
            }

            // Validate dữ liệu cơ bản
            $rules = [
                'title' => 'required|string|max:200',
                'description' => 'required|string|max:5000',
                'category' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'condition' => 'nullable|string|in:new,likenew,good,fair',
                'brand' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'location' => 'nullable|string|max:200',
                'quantity' => 'nullable|integer|min:1',
                'negotiable' => 'nullable|boolean',
                'shipping_method' => 'nullable|string|in:meet,ship,both',
                'shipping_fee' => 'nullable|numeric|min:0',
                'contact_phone' => 'nullable|string|max:20',
                'tags' => 'nullable|array',
            ];

            $messages = [
                'title.required' => 'Vui lòng nhập tiêu đề sản phẩm',
                'description.required' => 'Vui lòng nhập mô tả sản phẩm',
                'category.required' => 'Vui lòng chọn danh mục',
                'price.required' => 'Vui lòng nhập giá sản phẩm',
                'price.numeric' => 'Giá phải là số',
                'price.min' => 'Giá phải lớn hơn 0',
            ];

            // Validate images - có thể là array hoặc single file
            $hasImages = false;
            if ($req->hasFile('images')) {
                $hasImages = true;
                $files = $req->file('images');
                if (is_array($files)) {
                    $rules['images'] = 'required|array|min:1|max:10';
                    $rules['images.*'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:5120';
                } else {
                    $rules['images'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
                }
                $messages['images.required'] = 'Vui lòng thêm ít nhất 1 ảnh';
                $messages['images.min'] = 'Vui lòng thêm ít nhất 1 ảnh';
                $messages['images.max'] = 'Tối đa 10 ảnh';
                $messages['images.*.image'] = 'File phải là ảnh';
                $messages['images.*.max'] = 'Mỗi ảnh tối đa 5MB';
            } else {
                $rules['images'] = 'required';
                $messages['images.required'] = 'Vui lòng thêm ít nhất 1 ảnh';
            }

            $req->validate($rules, $messages);

            // Map condition từ frontend sang database
            $tinhTrangMap = [
                'new' => 'moi',
                'likenew' => 'moi',
                'good' => 'cu',
                'fair' => 'rat_cu',
            ];
            $tinhTrang = $tinhTrangMap[$req->condition] ?? 'moi';

            // Tìm danh mục theo category slug
            $danhMuc = null;
            if ($req->category) {
                $danhMuc = \App\Models\DanhMuc::where('slug', $req->category)->first();
            }

            // Xử lý ảnh - có thể là file upload hoặc URL/JSON array string
            $hinhAnhValue = null;
            if ($hasImages && $req->hasFile('images')) {
                // Đảm bảo thư mục tồn tại
                $productsDir = storage_path('app/public/products');
                if (!is_dir($productsDir)) {
                    mkdir($productsDir, 0755, true);
                }

                // Upload file(s) và lấy URL(s)
                $files = $req->file('images');
                $imageUrls = [];

                if (is_array($files)) {
                    // Upload nhiều file
                    foreach ($files as $file) {
                        $filename = $file->hashName();
                        $file->storeAs('products', $filename);
                        // Lưu relative path thay vì absolute URL để tương thích khi chia sẻ qua mạng
                        $imageUrls[] = '/storage/products/' . $filename;
                    }
                } else {
                    // Upload 1 file
                    $filename = $files->hashName();
                    $files->storeAs('products', $filename);
                    // Lưu relative path thay vì absolute URL để tương thích khi chia sẻ qua mạng
                    $imageUrls[] = '/storage/products/' . $filename;
                }

                // Luôn normalize thành JSON array string (kể cả 1 ảnh)
                $hinhAnhValue = $imageUrls;
            } elseif ($req->filled('hinh_anh')) {
                // Nhận từ request (có thể là URL string hoặc JSON array string)
                $hinhAnhValue = $req->hinh_anh;
            }

            // Tạo sản phẩm
            $sanPham = SanPham::create([
                'ten_san_pham' => trim($req->title),
                'mo_ta' => trim($req->description),
                'gia' => (float) $req->price,
                'hinh_anh' => SanPham::normalizeHinhAnh($hinhAnhValue),
                'tinh_trang' => $tinhTrang,
                'category' => $req->category,
                'danh_muc_id' => $danhMuc ? $danhMuc->id : null,
                'thuong_hieu' => $req->brand ?? null,
                'mau_sac' => null, // Có thể thêm sau
                'kich_thuoc' => null, // Có thể thêm sau
                'dia_chi' => $req->location ?? null,
                'tinh_thanh' => $req->location ?? null,
                'quan_huyen' => null,
                'khach_hang_id' => $user->id,
                'trang_thai' => 1, // 1: đang bán
                'luot_xem' => 0,
            ]);

            // Sử dụng helper method để không escape dấu /
            return $this->jsonResponse([
                'status' => true,
                'message' => 'Đăng bán sản phẩm thành công',
                'data' => [
                    'id' => $sanPham->id,
                    'ten_san_pham' => $sanPham->ten_san_pham,
                    'gia' => $sanPham->gia,
                    'hinh_anh' => $sanPham->hinh_anh,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy thống kê sản phẩm của seller (đơn hàng, doanh thu, đánh giá)
     */
    public function getSellerProductStats(Request $req)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Lấy tất cả sản phẩm của seller
        $productIds = SanPham::where('khach_hang_id', $user->id)->pluck('id');

        if ($productIds->isEmpty()) {
            return response()->json([
                'status' => true,
                'data' => [],
            ]);
        }

        // Tối ưu: Load tất cả orders và reviews một lần thay vì N+1 queries
        $allOrders = \App\Models\DonHang::whereIn('san_pham_id', $productIds)
            ->get()
            ->groupBy('san_pham_id');

        $allReviews = \App\Models\DanhGia::whereIn('san_pham_id', $productIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('san_pham_id');

        // Lấy sản phẩm
        $products = SanPham::whereIn('id', $productIds)->get();

        $stats = [];

        foreach ($products as $product) {
            // Lấy đơn hàng từ cache
            $productOrders = $allOrders->get($product->id, collect());

            // Đếm số đơn hàng đã thanh toán thành công
            $completedOrders = $productOrders->filter(function($order) {
                return in_array($order->status, ['completed', 'delivered'])
                    && in_array($order->payment_status, ['paid', 'completed']);
            });

            $totalOrders = $completedOrders->count();
            $totalRevenue = $completedOrders->sum('tong_tien');

            // Lấy đánh giá từ cache
            $reviews = $allReviews->get($product->id, collect());
            $avgRating = $reviews->avg('diem') ?? 0;

            // Lấy danh sách người mua từ tất cả đơn hàng (không chỉ đơn đã thanh toán)
            $buyers = $productOrders->map(function($order) {
                return [
                    'name' => $order->buyer_name,
                    'phone' => $order->buyer_phone,
                    'email' => $order->buyer_email,
                    'address' => $order->shipping_address,
                    'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                    'order_code' => $order->ma_don_hang,
                    'quantity' => $order->so_luong,
                    'total' => (float) $order->tong_tien,
                ];
            })->values();

            $stats[] = [
                'product_id' => $product->id,
                'product_name' => $product->ten_san_pham,
                'total_orders' => $totalOrders,
                'total_revenue' => (float) $totalRevenue,
                'average_rating' => round($avgRating, 2),
                'total_reviews' => $reviews->count(),
                'buyers' => $buyers,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Lấy đơn hàng của một sản phẩm cụ thể
     */
    public function getProductOrders(Request $req, SanPham $san_pham)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền sở hữu
        if ($san_pham->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền xem đơn hàng của sản phẩm này',
            ], 403);
        }

        $orders = \App\Models\DonHang::where('san_pham_id', $san_pham->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->ma_don_hang,
                    'buyer_name' => $order->buyer_name,
                    'buyer_phone' => $order->buyer_phone,
                    'buyer_email' => $order->buyer_email,
                    'shipping_address' => $order->shipping_address,
                    'quantity' => $order->so_luong,
                    'total' => (float) $order->tong_tien,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Lấy đánh giá của một sản phẩm
     */
    public function getProductReviews(Request $req, SanPham $san_pham)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền sở hữu
        if ($san_pham->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền xem đánh giá của sản phẩm này',
            ], 403);
        }

        $reviews = \App\Models\DanhGia::where('san_pham_id', $san_pham->id)
            ->where('is_active', true)
            ->with('khachHang:id,ho_va_ten,email')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->diem,
                    'comment' => $review->noi_dung,
                    'buyer_name' => $review->khachHang ? $review->khachHang->ho_va_ten : 'Ẩn danh',
                    'buyer_email' => $review->khachHang ? $review->khachHang->email : null,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $avgRating = $reviews->avg('rating') ?? 0;

        return response()->json([
            'status' => true,
            'data' => [
                'reviews' => $reviews,
                'average_rating' => round($avgRating, 2),
                'total_reviews' => $reviews->count(),
            ],
        ]);
    }

    /**
     * Sửa sản phẩm (SELLER - chỉ có thể sửa sản phẩm của mình)
     */
    public function updateSellerProduct(Request $req, SanPham $san_pham)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền sở hữu
        if ($san_pham->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền sửa sản phẩm này',
            ], 403);
        }

        try {
            $data = [];

            if ($req->filled('title')) {
                $data['ten_san_pham'] = trim($req->title);
            }
            if ($req->filled('description')) {
                $data['mo_ta'] = trim($req->description);
            }
            if ($req->filled('price')) {
                $data['gia'] = (float) $req->price;
            }
            if ($req->filled('category')) {
                $data['category'] = $req->category;
                $danhMuc = \App\Models\DanhMuc::where('slug', $req->category)->first();
                $data['danh_muc_id'] = $danhMuc ? $danhMuc->id : null;
            }
            if ($req->filled('condition')) {
                $tinhTrangMap = [
                    'new' => 'moi',
                    'likenew' => 'moi',
                    'good' => 'cu',
                    'fair' => 'rat_cu',
                ];
                $data['tinh_trang'] = $tinhTrangMap[$req->condition] ?? 'moi';
            }
            if ($req->filled('location')) {
                $data['tinh_thanh'] = $req->location;
                $data['dia_chi'] = $req->location;
            }
            if ($req->has('trang_thai')) {
                $data['trang_thai'] = $req->trang_thai; // 1: đang bán, 3: ẩn
            }

            // Xử lý ảnh nếu có
            if ($req->hasFile('images')) {
                // Đảm bảo thư mục tồn tại
                $productsDir = storage_path('app/public/products');
                if (!is_dir($productsDir)) {
                    mkdir($productsDir, 0755, true);
                }

                // Upload file(s) và lấy URL(s)
                $files = $req->file('images');
                $imageUrls = [];

                if (is_array($files)) {
                    // Upload nhiều file
                    foreach ($files as $file) {
                        $filename = $file->hashName();
                        $file->storeAs('products', $filename);
                        // Lưu relative path thay vì absolute URL để tương thích khi chia sẻ qua mạng
                        $imageUrls[] = '/storage/products/' . $filename;
                    }
                } else {
                    // Upload 1 file
                    $filename = $files->hashName();
                    $files->storeAs('products', $filename);
                    // Lưu relative path thay vì absolute URL để tương thích khi chia sẻ qua mạng
                    $imageUrls[] = '/storage/products/' . $filename;
                }

                // Normalize thành JSON array string
                $data['hinh_anh'] = SanPham::normalizeHinhAnh($imageUrls);
            } elseif ($req->filled('hinh_anh')) {
                // Nhận từ request (có thể là URL string hoặc JSON array string)
                $data['hinh_anh'] = SanPham::normalizeHinhAnh($req->hinh_anh);
            }

            $san_pham->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật sản phẩm thành công',
                'data' => $san_pham->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm (SELLER - chỉ có thể xóa sản phẩm của mình)
     */
    public function deleteSellerProduct(Request $req, SanPham $san_pham)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra quyền sở hữu
        if ($san_pham->khach_hang_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền xóa sản phẩm này',
            ], 403);
        }

        // Xóa ảnh cũ nếu cần (có thể implement sau)
        // if ($san_pham->hinh_anh) {
        //     $images = $san_pham->getImagesArray();
        //     foreach ($images as $imageUrl) {
        //         // Xóa file nếu là local storage
        //     }
        // }

        $san_pham->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa sản phẩm thành công',
        ]);
    }

    /**
     * Lấy lịch sử bán hàng của seller (tất cả đơn hàng từ tất cả sản phẩm)
     * Bao gồm: người mua, sản phẩm, số lượng, giá, thời gian, đánh giá
     */
    public function getSellerSalesHistory(Request $req)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Pagination
        $perPage = min((int) $req->get('per_page', 20), 100);

        // Lấy tất cả sản phẩm của seller
        $productIds = SanPham::where('khach_hang_id', $user->id)->pluck('id');

        if ($productIds->isEmpty()) {
            return response()->json([
                'status' => true,
                'data' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
            ]);
        }

        // Lấy tất cả đơn hàng từ các sản phẩm của seller với pagination
        $query = \App\Models\DonHang::whereIn('san_pham_id', $productIds)
            ->with(['sanPham:id,ten_san_pham,gia,hinh_anh', 'khachHang:id,ho_va_ten,email'])
            ->orderBy('created_at', 'desc');

        $paginated = $query->paginate($perPage);
        $orders = $paginated->getCollection();

        // Lấy tất cả đánh giá của các sản phẩm
        $reviews = \App\Models\DanhGia::whereIn('san_pham_id', $productIds)
            ->where('is_active', true)
            ->with(['sanPham:id,ten_san_pham', 'khachHang:id,ho_va_ten,email'])
            ->get()
            ->keyBy(function($review) {
                // Key theo format: product_id-khach_hang_id để match với đơn hàng
                return $review->san_pham_id . '-' . ($review->khach_hang_id ?? 'guest');
            });

        // Format dữ liệu
        $salesHistory = $orders->map(function($order) use ($reviews) {
            // Tìm đánh giá tương ứng
            $reviewKey = $order->san_pham_id . '-' . ($order->khach_hang_id ?? 'guest');
            $review = $reviews->get($reviewKey);

            // Lấy ảnh sản phẩm
            $productImages = $order->sanPham->getImagesArray();

            return [
                'order_id' => $order->id,
                'order_code' => $order->ma_don_hang,
                'order_date' => $order->created_at->format('Y-m-d H:i:s'),

                // Thông tin người mua
                'buyer_id' => $order->khach_hang_id,
                'buyer_name' => $order->buyer_name,
                'buyer_email' => $order->buyer_email,
                'buyer_phone' => $order->buyer_phone,
                'buyer_address' => $order->shipping_address,

                // Thông tin sản phẩm
                'product_id' => $order->san_pham_id,
                'product_name' => $order->sanPham ? $order->sanPham->ten_san_pham : 'Sản phẩm đã bị xóa',
                'product_image' => !empty($productImages) ? $productImages[0] : null,
                'product_price' => $order->sanPham ? (float) $order->sanPham->gia : 0,

                // Thông tin đơn hàng
                'quantity' => $order->so_luong,
                'total_amount' => (float) $order->tong_tien,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->status,

                // Đánh giá
                'review' => $review ? [
                    'id' => $review->id,
                    'rating' => $review->diem,
                    'comment' => $review->noi_dung,
                    'reviewer_name' => $review->khachHang ? $review->khachHang->ho_va_ten : 'Ẩn danh',
                    'reviewer_email' => $review->khachHang ? $review->khachHang->email : null,
                    'reviewed_at' => $review->created_at->format('Y-m-d H:i:s'),
                ] : null,
            ];
        });

        // Tính tổng doanh thu từ tất cả đơn hàng (không chỉ trang hiện tại)
        $allOrdersForSummary = \App\Models\DonHang::whereIn('san_pham_id', $productIds)->get();
        $totalRevenue = $allOrdersForSummary->sum('tong_tien');
        $totalOrders = $allOrdersForSummary->count();
        $completedOrders = $allOrdersForSummary->whereIn('payment_status', ['paid', 'completed'])->sum('tong_tien');

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $salesHistory->values(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'summary' => [
                    'total_revenue' => (float) $totalRevenue,
                    'total_orders' => $totalOrders,
                    'completed_revenue' => (float) $completedOrders,
                    'average_order_value' => $totalOrders > 0 ? (float) ($totalRevenue / $totalOrders) : 0,
                ],
            ],
        ]);
    }
}
