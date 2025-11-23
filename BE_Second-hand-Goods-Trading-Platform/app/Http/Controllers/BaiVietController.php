<?php

namespace App\Http\Controllers;

use App\Models\BaiViet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaiVietController extends Controller
{
    /**
     * Danh sách bài viết
     */
    public function index(Request $req)
    {
        $perPage = min((int) $req->get('per_page', 20), 100);
        $q = BaiViet::query();

        if ($req->filled('is_active') && $req->is_active !== 'all') {
            $q->where('is_active', (int) $req->is_active);
        }

        if ($req->filled('q')) {
            $kw = '%' . trim($req->q) . '%';
            $q->where(function ($k) use ($kw) {
                $k->where('tieu_de', 'like', $kw)
                  ->orWhere('noi_dung', 'like', $kw);
            });
        }

        $q->orderByDesc('created_at');

        return response()->json([
            'status' => true,
            'data' => $q->paginate($perPage),
        ]);
    }

    /**
     * Chi tiết bài viết
     */
    public function show(BaiViet $bai_viet)
    {
        // Tăng lượt xem
        $bai_viet->increment('luot_xem');
        
        return response()->json([
            'status' => true,
            'data' => $bai_viet,
        ]);
    }

    /**
     * Tạo bài viết mới
     */
    public function store(Request $req)
    {
        $req->validate([
            'tieu_de' => 'required|string|max:200',
            'noi_dung' => 'required|string',
            'hinh_anh' => 'nullable|string|url',
            'is_active' => 'nullable|boolean',
        ], [
            'tieu_de.required' => 'Vui lòng nhập tiêu đề',
            'noi_dung.required' => 'Vui lòng nhập nội dung',
        ]);

        $user = Auth::guard('sanctum')->user();
        
        $bai_viet = BaiViet::create([
            'tieu_de' => trim($req->tieu_de),
            'noi_dung' => trim($req->noi_dung),
            'hinh_anh' => $req->hinh_anh ?? null,
            'is_active' => $req->is_active ?? true,
            'khach_hang_id' => $user ? $user->id : null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo bài viết thành công',
            'data' => $bai_viet,
        ], 201);
    }

    /**
     * Cập nhật bài viết
     */
    public function update(Request $req, BaiViet $bai_viet)
    {
        $req->validate([
            'tieu_de' => 'sometimes|required|string|max:200',
            'noi_dung' => 'sometimes|required|string',
            'hinh_anh' => 'nullable|string|url',
            'is_active' => 'nullable|boolean',
        ]);

        $bai_viet->update($req->only(['tieu_de', 'noi_dung', 'hinh_anh', 'is_active']));

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật bài viết thành công',
            'data' => $bai_viet->fresh(),
        ]);
    }

    /**
     * Xóa bài viết
     */
    public function destroy(BaiViet $bai_viet)
    {
        $bai_viet->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa bài viết thành công',
        ]);
    }

    /**
     * Bật/Tắt trạng thái bài viết
     */
    public function toggleActive(BaiViet $bai_viet)
    {
        $bai_viet->is_active = !$bai_viet->is_active;
        $bai_viet->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật trạng thái',
            'data' => $bai_viet,
        ]);
    }
}
