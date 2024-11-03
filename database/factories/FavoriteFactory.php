<?php

namespace Database\Factories;

use App\Models\Favorite;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductDetails;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition()
    {
        $user = User::inRandomOrder()->first();  
        $product = Product::inRandomOrder()->first();  

        $productDetail = ProductDetails::where('product_id', $product->id)
                                       ->inRandomOrder()
                                       ->first();

        return [
            'user_id' => $user ? $user->id : User::factory(),
            'product_id' => $product ? $product->id : Product::factory(),
            'product_detail_id' => $productDetail ? $productDetail->id : ProductDetails::factory(['product_id' => $product->id]),
        ];
    }
}
