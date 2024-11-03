<?php

namespace Database\Factories;

use App\Models\ProductDetails;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductDetailsFactory extends Factory
{
    protected $model = ProductDetails::class;

    public function definition()
    {
        $product = Product::inRandomOrder()->first();  

        return [
            'size' => $this->faker->word(),
            'color' => $this->faker->colorName(),
            'image' => $this->faker->imageUrl(),
            'price' => $this->faker->randomFloat(2, 120, 500),
            'discount' => $this->faker->numberBetween(2, 50),
            'stock' => $this->faker->numberBetween(1, 100),
            'product_id' => $product ? $product->id : Product::factory(), 
        ];
    }
}
