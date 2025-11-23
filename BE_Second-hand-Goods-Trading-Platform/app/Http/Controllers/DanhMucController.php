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
}




