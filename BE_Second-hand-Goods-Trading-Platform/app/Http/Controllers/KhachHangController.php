<?php

namespace App\Http\Controllers;

use App\Exports\KhachHangExport;
use App\Mail\MasterMail;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class KhachHangController extends Controller
{
    public function getData()
    {
        $data = KhachHang::all();
        return response()->json([
            'data' => $data
        ]);
    }

    public function addData(Request $request)
    {
        KhachHang::create([
            'ho_va_ten'     => $request->ho_va_ten,
            'email'         => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'password'      => Hash::make('123456'),
            'cccd'          => $request->cccd,
            'ngay_sinh'     => $request->ngay_sinh,
            'is_block'      => 0,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Thêm khách hàng ' . $request->ho_va_ten . ' thành công',
        ]);
    }

    public function update(Request $request)
    {
        KhachHang::where('id', $request->id)->update([
            'ho_va_ten'     => $request->ho_va_ten,
            'email'         => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'password'      => Hash::make($request->password),
            'cccd'          => $request->cccd,
            'ngay_sinh'     => $request->ngay_sinh,
            'is_block'      => $request->is_block,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật khách hàng ' . $request->ho_va_ten . ' thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        KhachHang::where('id', $request->id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa khách hàng thành công',
        ]);
    }

    public function changeStatus(Request $request)
    {
        $nhan_vien = KhachHang::where('id', $request->id)->first();
        $nhan_vien->is_block = !$nhan_vien->is_block;
        $nhan_vien->save();

        return response()->json([
            'status' => true,
            'message' => 'Thay đổi trạng thái thành công',
        ]);
    }

    public function changeActive(Request $request)
    {
        $nhan_vien = KhachHang::where('id', $request->id)->first();
        $nhan_vien->is_active = !$nhan_vien->is_active;
        $nhan_vien->save();

        return response()->json([
            'status' => true,
            'message' => 'Thay đổi trạng thái thành công',
        ]);
    }

    public function dangNhap(Request $request)
    {
        $user = KhachHang::where('email', $request->email)->first();

        if ($user) {
            // Check password
            if (Hash::check($request->password, $user->password)) {
                // Password match (Hashed)
            } elseif ($user->password === $request->password) {
                // Password match (Plain text - Legacy) -> Update to Hash
                $user->password = Hash::make($request->password);
                $user->save();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản sai email hoặc password',
                ]);
            }

            if ($user->is_active == 0) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Tài khoản chưa được kích hoạt',
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Đăng nhập thành công',
                    'token' => $user->createToken('key_client')->plainTextToken,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản sai email hoặc password',
            ]);
        }
    }

    public function checkClient()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            return response()->json([
                'status' => true,
                'ho_ten' => $user->ho_va_ten,
                'email' => $user->email
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền truy cập.',
            ]);
        }
    }

    public function kichHoat(Request $request)
    {
        $user = KhachHang::where('hash_active', $request->hash_active)->update([
            'is_active' => 1,
            'hash_active' => null
        ]);
        return response()->json([
            'status'    =>  1,
            'message'   =>  'Đã kích hoạt tài khoản thành công'
        ]);
    }

    public function dangKy(Request $request)
    {
        $key = Str::uuid();
        $khachHang = KhachHang::create([
            'ho_va_ten'     => $request->ho_va_ten,
            'email'         => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'password'      => Hash::make($request->password),
            'cccd'          => $request->cccd,
            'ngay_sinh'     => $request->ngay_sinh,
            'is_block'      => 0,
            'is_active'     => 0,
            'hash_active'   => $key,
        ]);

        $tieu_de = "Kích hoạt tài khoản";
        $view = "kichHoatTK";
        $noi_dung['ho_va_ten'] = $khachHang->ho_va_ten;
        // đổi api
        $noi_dung['link'] = "http://192.168.1.229:5173//client/kich-hoat/" . $key;
        Mail::to($request->email)->send(new MasterMail($tieu_de, $view, $noi_dung));

        return response()->json([
            'status' => true,
            'message' => 'Đã đăng ký tài khoản thành công. Vui lòng kiểm tra email',
        ]);
    }

    public function quenMatKhau(Request $request)
    {
        $user = KhachHang::where('email', $request->email)->first();

        if ($user) {
            $key = Str::uuid();
            $tieu_de = "Quên mật khẩu";
            $view = "quenMatKhau";
            $noi_dung['ho_va_ten'] = $user->ho_va_ten;
            $noi_dung['link'] = "http://localhost:5173/client/dat-lai-mat-khau/" . $key;

            $user->hash_reset = $key;
            $user->save();

            Mail::to($user->email)->send(new MasterMail($tieu_de, $view, $noi_dung));

            return response()->json([
                'status' => true,
                'message' => 'Vui lòng kiểm tra email để đổi mật khẩu.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản chưa được đăng ký.',
            ]);
        }
    }

    public function datLaiMatKhau(Request $request)
    {
        $user = KhachHang::where('hash_reset', $request->hash_reset)->first();

        if ($request->password != $request->re_password) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.',
            ]);
        }

        $user->update([
            'password'      =>  Hash::make($request->password),
            'hash_reset'    =>  null
        ]);

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đổi mật khẩu thành công.',
        ]);
    }

    public function thongTinNguoiDung()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user && $user instanceof \App\Models\KhachHang) {
            return response()->json([
                'data'  => $user,
            ]);
        }
    }

    public function doiMatKhau(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if ($user && $user instanceof \App\Models\KhachHang) {

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'    =>  0,
                    'message'   =>  'Mật khẩu hiện tại không đúng. Vui lòng nhập lại.'
                ]);
            } else {
                if ($request->new_password != $request->confirm_password) {
                    return response()->json([
                        'status'    =>  0,
                        'message'   =>  'Mật khẩu hiện mới và xác nhận không khớp. Vui lòng nhập lại.'
                    ]);
                } else {
                    $user->password = Hash::make($request->new_password);
                    $user->save();

                    return response()->json([
                        'status'    =>  1,
                        'message'   =>  'Đổi mật khẩu thành công.'
                    ]);
                }
            }
        }
    }


    // public function xuatExcel()
    // {
    //     $data = KhachHang::all();


    //     return Excel::download(new KhachHangExport($data), "khach_hang.xlsx");
    // }

    public function dangXuat()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            DB::table('personal_access_tokens')
                ->where('id', $user->currentAccessToken()->id)
                ->delete();
            return response()->json([
                'status'  => 1,
                'message' => "Đăng xuất thành công",
            ]);
        } else {
            return response()->json([
                'status'  => 0,
                'message' => "Có lỗi xảy ra",
            ]);
        }
    }

    public function search(Request $request)
    {
        $noi_dung = '%' . $request->noi_dung . '%';

        $data = KhachHang::where('email', 'like', $noi_dung)
            ->orWhere('ho_va_ten', 'like', $noi_dung)
            ->orWhere('so_dien_thoai', 'like', $noi_dung)
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Đăng ký bán hàng - User đồng ý với điều khoản và được phép đăng bán
     */
    public function dangKyBan(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập để đăng ký bán hàng',
            ], 401);
        }

        // Kiểm tra xem user đã đăng ký bán chưa
        if ($user->is_seller == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Bạn đã đăng ký bán hàng rồi',
                'is_seller' => true,
            ]);
        }

        // Kiểm tra user có đồng ý với điều khoản không
        if (!$request->has('agreed_to_terms') || !$request->agreed_to_terms) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn cần đồng ý với điều khoản đăng ký bán hàng',
            ], 400);
        }

        // Cập nhật trạng thái đăng ký bán
        $user->is_seller = 1;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đăng ký bán hàng thành công! Bạn có thể bắt đầu đăng bán sản phẩm.',
            'is_seller' => true,
        ]);
    }

    /**
     * Kiểm tra trạng thái đăng ký bán hàng
     */
    public function checkSellerStatus()
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'is_seller' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'is_seller' => $user->is_seller == 1,
            'message' => $user->is_seller == 1 ? 'Bạn đã đăng ký bán hàng' : 'Bạn chưa đăng ký bán hàng',
        ]);
    }

    /**
     * Lấy thông tin profile của user đang đăng nhập
     */
    public function getProfile()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'data' => $user,
        ]);
    }

    /**
     * Cập nhật thông tin profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $user->update([
            'ho_va_ten' => $request->ho_va_ten ?? $user->ho_va_ten,
            'email' => $request->email ?? $user->email,
            'so_dien_thoai' => $request->so_dien_thoai ?? $user->so_dien_thoai,
            'gioi_tinh' => $request->gioi_tinh ?? $user->gioi_tinh,
            'ngay_sinh' => $request->ngay_sinh ?? $user->ngay_sinh,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật hồ sơ thành công',
            'data' => $user,
        ]);
    }

    /**
     * Cập nhật thông tin ngân hàng
     */
    public function updateBank(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $user->update([
            'ten_ngan_hang' => $request->name ?? $request->ten_ngan_hang ?? $user->ten_ngan_hang,
            'so_tai_khoan' => $request->number ?? $request->so_tai_khoan ?? $user->so_tai_khoan,
            'chu_tai_khoan' => $request->owner ?? $request->chu_tai_khoan ?? $user->chu_tai_khoan,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông tin ngân hàng thành công',
            'data' => $user,
        ]);
    }

    /**
     * Cập nhật thông tin địa chỉ
     */
    public function updateAddress(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $user->update([
            'dia_chi_ho_ten' => $request->name ?? $request->dia_chi_ho_ten ?? $user->dia_chi_ho_ten,
            'dia_chi_so_dien_thoai' => $request->phone ?? $request->dia_chi_so_dien_thoai ?? $user->dia_chi_so_dien_thoai,
            'dia_chi_chi_tiet' => $request->full ?? $request->dia_chi_chi_tiet ?? $user->dia_chi_chi_tiet,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $user,
        ]);
    }

    /**
     * Đổi mật khẩu (tương thích với frontend)
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        // Kiểm tra thời gian đổi mật khẩu gần nhất
        if ($user->last_password_change_at) {
            $lastChange = \Carbon\Carbon::parse($user->last_password_change_at);
            $now = \Carbon\Carbon::now();
            $diffInMinutes = $now->diffInMinutes($lastChange);

            if ($diffInMinutes < 30) {
                $remainingMinutes = 30 - $diffInMinutes;
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn vừa đổi mật khẩu. Vui lòng thử lại sau ' . $remainingMinutes . ' phút nữa.',
                ], 429); // 429 Too Many Requests
            }
        }

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu hiện tại không đúng',
            ], 400);
        }

        // Kiểm tra mật khẩu mới và xác nhận
        if ($request->new_password != $request->confirm_password) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu mới và xác nhận không khớp',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->last_password_change_at = now(); // Cập nhật thời gian đổi
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đổi mật khẩu thành công',
        ]);
    }
}
