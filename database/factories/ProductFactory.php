<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $this->faker->locale(); // Set the locale to English

        return [
        //    "product_name" =>$this->faker->word,
        "product_name"=>"product"+ $this->faker->numberBetween(1,100),
            // 'category_id' => \App\Models\Category::factory(),
            'category_id'=>$this->faker->numberBetween(1,5),
            'description' => $this->faker->sentence,
            'regular_price' => $this->faker->randomFloat(2, 0, 1000),
            'brand' => $this->faker->company,
            'product_img1' => $this->faker->imageUrl(),
            'product_img2' => $this->faker->imageUrl(),
            'product_img3' => $this->faker->imageUrl(),
            'product_img4' => $this->faker->imageUrl(),
            'product_img5' => $this->faker->imageUrl(),
            'weight' => $this->faker->randomFloat(2, 0, 100),
            'quantity_in_stock' => $this->faker->numberBetween(0, 100),
            'tags' => $this->faker->words(3, true),
            'refundable' => $this->faker->boolean,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'sales_price' => $this->faker->randomFloat(2, 0, 1000),
            'meta_title' => $this->faker->sentence,
            'meta_description' => $this->faker->paragraph,
        ];
    }
}