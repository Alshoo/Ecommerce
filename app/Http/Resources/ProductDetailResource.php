<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $finalPrice = $this->discount ? $this->price - ($this->price * ($this->discount / 100)) : $this->price;

        return [
            'id' => $this->id,
            'size' => $this->size,
            'color' => $this->color,
            'image' => $this->image,
            'original_price' => floatval($this->price), 
            'final_price' => floatval($finalPrice), 
            'discount' => $this->discount ? floatval($this->discount) . '%' : null,
            'stock' => intval($this->stock),
        ];
    }
}
