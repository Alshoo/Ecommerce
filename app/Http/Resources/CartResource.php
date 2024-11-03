<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $productDetails = $this->product->details;

        $originalPrice = $this->productDetail->price;
        $discount = $this->productDetail->discount;

        $finalPrice = $originalPrice;
        if (!is_null($discount) && $discount > 0) {
            $finalPrice = $originalPrice - ($originalPrice * ($discount / 100)); 
        }

        return [
            'id' => $this->id,
            'quantity' => intval($this->quantity),
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'description' => $this->product->description,
            'brand' => $this->product->brand,
            'categories' => $this->product->categories->pluck('category_name')->implode(', '),
            'details_id' => $this->productDetail->id,
            'stock' => intval($this->productDetail->stock),
            'discount' => $discount,
            'image' => $this->productDetail->image,
            'price' => round(floatval($finalPrice), 2), 
            'color' => $this->productDetail->color,
            'size' => $this->productDetail->size,
        ];
    }
}
