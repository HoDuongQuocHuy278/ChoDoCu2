<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:200',
            'price'       => 'sometimes|integer|min:0',
            'discount'    => 'sometimes|integer|min:0|max:100',
            'sold'        => 'sometimes|integer|min:0',
            'category'    => 'sometimes|string|max:100',
            'rating'      => 'sometimes|integer|min:0|max:5',
            'description' => 'sometimes|nullable|string',
            'detail'      => 'sometimes|nullable|string',
            'is_active'   => 'sometimes|boolean',

            'image'       => 'sometimes|nullable|url',
            'image_file'  => 'sometimes|nullable|image|max:4096',
        ];
    }
}
