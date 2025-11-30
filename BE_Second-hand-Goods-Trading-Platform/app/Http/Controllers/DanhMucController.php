<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use App\Models\SanPham;
use Illuminate\Http\Request;

class DanhMucController extends Controller
{
    /**
     * Lấy danh sách tất cả danh mục
     */
    public function index()
    {
        $danhMucs = DanhMuc::where('is_active', true)
            ->orderBy('thu_tu')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $danhMucs,
        ]);
    }

    /**
     * Lấy chi tiết danh mục và sản phẩm trong danh mục
     */
    public function show($slug, Request $req)
    {
        $danhMuc = DanhMuc::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $perPage = min((int) $req->get('per_page', 20), 100);

        $sanPhams = SanPham::where('danh_muc_id', $danhMuc->id)
            ->where('trang_thai', 1)
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => [
                'danh_muc' => $danhMuc,
                'san_phams' => $sanPhams,
            ],
        ]);
    }

    /**
     * Lấy sản phẩm theo danh mục (API riêng)
     */
    public function sanPham($slug, Request $req)
    {
        $danhMuc = DanhMuc::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $perPage = min((int) $req->get('per_page', 20), 100);

        $q = SanPham::query()
            ->where('danh_muc_id', $danhMuc->id)
            ->where('trang_thai', 1);

        // Filter theo giá
        if ($req->filled('price_min')) {
            $q->where('gia', '>=', (int) $req->price_min);
        }
        if ($req->filled('price_max')) {
            $q->where('gia', '<=', (int) $req->price_max);
        }

        // Sort
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

        $sanPhams = $q->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $sanPhams,
        ]);
    }
    // ADMIN METHODS

    public function store(Request $request)
    {
        $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'hinh_anh' => 'nullable|string',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->ten_danh_muc);

        $danhMuc = DanhMuc::create([
            'ten_danh_muc' => $request->ten_danh_muc,
            'slug' => $slug,
            'hinh_anh' => $request->hinh_anh,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo danh mục thành công',
            'data' => $danhMuc,
        ]);
    }

    public function update(Request $request, DanhMuc $danhMuc)
    {
        $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'hinh_anh' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('ten_danh_muc')) {
            $danhMuc->ten_danh_muc = $request->ten_danh_muc;
            $danhMuc->slug = \Illuminate\Support\Str::slug($request->ten_danh_muc);
        }
        if ($request->filled('hinh_anh')) {
            $danhMuc->hinh_anh = $request->hinh_anh;
        }
        if ($request->has('is_active')) {
            $danhMuc->is_active = $request->is_active;
        }
        $danhMuc->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật danh mục thành công',
            'data' => $danhMuc,
        ]);
    }

    public function destroy(DanhMuc $danhMuc)
    {
        // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
        if ($danhMuc->sanPhams()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể xóa danh mục đã có sản phẩm',
            ], 400);
        }

        $danhMuc->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa danh mục thành công',
        ]);
    }
}




