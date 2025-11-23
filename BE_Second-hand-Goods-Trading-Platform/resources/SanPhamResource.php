<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SanPhamResource extends JsonResource
{
    public function toArray($request)
    {
        $finalPrice = (int) round($this->price * (100 - $this->discount) / 100);

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'price'       => (int) $this->price,
            'discount'    => (int) $this->discount,
            'final_price' => $finalPrice,
            'sold'        => (int) $this->sold,
            'category'    => $this->category,
            'rating'      => (int) $this->rating,
            'image'       => $this->image,
            'description' => $this->description,
            'detail'      => $this->detail,
            'is_active'   => (bool) $this->is_active,
            'created_at'  => optional($this->created_at)->toISOString(),
            'updated_at'  => optional($this->updated_at)->toISOString(),
        ];
    }
}
