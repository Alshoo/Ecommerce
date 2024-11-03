<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'description' => $this->product->description,
            'brand' => $this->product->brand,
            'categories' => $this->product->categories->pluck('category_name')->implode(', '),
            'details_id'  => $this->productDetail->id,
            'stock' => intval($this->productDetail->stock),
            'discount' => $this->productDetail->discount,
            'image' => $this->productDetail->image,
            'price' => floatval($this->productDetail->price),
            'color' => $this->productDetail->color,
            'size' => $this->productDetail->size,
        ];
    }
}
