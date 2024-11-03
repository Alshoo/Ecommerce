<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userFavorite = $this->favorites()->where('user_id', Auth::id())->first();
        
        $firstDetail = $this->details->first();

        if ($firstDetail) {
            $originalPrice = $firstDetail['price'];
            $discount = $firstDetail['discount'];
            $finalPrice = $originalPrice - ($originalPrice * ($discount / 100)); 
        } else {
            $originalPrice = null;
            $discount = null;
            $finalPrice = null;
        }

        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'description' => $this->description,
            'brand' => $this->brand,
            'vendor_id' => $this->vendor->id,
            'vendor_name' => $this->vendor->name,
            'vendor_image' => $this->vendor->image,
            'categories' => $this->categories->pluck('category_name')->implode(', '),
            'size' => $firstDetail['size'] ?? null, 
            'color' => $firstDetail['color'] ?? null, 
            'image' => $firstDetail['image'] ?? null, 
            'original_price' => $originalPrice,
            'final_price' => $finalPrice ? round($finalPrice, 2) : null, 
            'discount' => $discount,
            'stock' => $firstDetail ? intval($firstDetail['stock']) : null,
            'is_favorite' => $userFavorite !== null, 
            'favorite_id' => $userFavorite ? $userFavorite->id : null, 
        ];
    }
}
