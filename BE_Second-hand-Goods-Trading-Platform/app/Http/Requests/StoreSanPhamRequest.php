<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tùy bạn: có thể kiểm tra role admin ở đây
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:200',
            'price'       => 'required|integer|min:0',
            'discount'    => 'nullable|integer|min:0|max:100',
            'sold'        => 'nullable|integer|min:0',
            'category'    => 'required|string|max:100',
            'rating'      => 'nullable|integer|min:0|max:5',
            'description' => 'nullable|string',
            'detail'      => 'nullable|string',
            'is_active'   => 'nullable|boolean',

            // ảnh: chọn 1 trong 2
            'image'       => 'nullable|url',
            'image_file'  => 'nullable|image|max:4096', // jpg,png,webp...
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Tên sản phẩm là bắt buộc',
            'price.required' => 'Giá là bắt buộc',
            'price.integer'  => 'Giá phải là số nguyên (VND)',
            'image.url'      => 'Ảnh (URL) không hợp lệ',
            'image_file.image' => 'Tệp tải lên phải là ảnh',
        ];
    }
}
