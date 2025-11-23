<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class CreateClientKhachHangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'ho_va_ten'     => 'required|string|max:255',
            'email'         => 'required|email|unique:khach_hangs,email',
            'so_dien_thoai' => 'required|digits:10|unique:khach_hangs,so_dien_thoai',
            'password'      => 'required|string|min:6|confirmed',
            're_password'   => 'required|same:password',
            'cccd'          => 'required|digits:12|unique:khach_hangs,cccd',
            'ngay_sinh'     => 'required|date|before:today',
        ];
    }


    public function messages()
    {
        return [
            'ho_va_ten.required'       => 'Vui lòng nhập họ và tên.',
            'ho_va_ten.string'         => 'Họ và tên phải là chuỗi ký tự.',
            'ho_va_ten.max'            => 'Họ và tên không được vượt quá 255 ký tự.',

            'email.required'           => 'Vui lòng nhập email.',
            'email.email'              => 'Email không đúng định dạng.',
            'email.unique'             => 'Email đã được sử dụng.',

            'so_dien_thoai.required'   => 'Vui lòng nhập số điện thoại.',
            'so_dien_thoai.digits_between' => 'Số điện thoại phải từ 9 đến 11 chữ số.',
            'so_dien_thoai.unique'     => 'Số điện thoại đã được sử dụng.',

            'password.required'        => 'Vui lòng nhập mật khẩu.',
            'password.string'          => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min'             => 'Mật khẩu phải có ít nhất 6 ký tự.',

            're_password.required'     => 'Vui lòng nhập lại mật khẩu.',
            're_password.same'         => 'Mật khẩu nhập lại không khớp.',

            'cccd.required'            => 'Vui lòng nhập CCCD.',
            'cccd.digits'              => 'CCCD phải gồm đúng 12 chữ số.',
            'cccd.unique'              => 'CCCD đã tồn tại trong hệ thống.',

            'ngay_sinh.required'       => 'Vui lòng nhập ngày sinh.',
            'ngay_sinh.date'           => 'Ngày sinh không hợp lệ.',
            'ngay_sinh.before'         => 'Ngày sinh phải trước ngày hôm nay.',
        ];
    }
}
