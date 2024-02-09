<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //    // Define the number of dummy products you want to create
    //    $numberOfProducts = 10;

    //    // Create dummy products
    //    \App\Models\Product::factory($numberOfProducts)->create();
    // }





    public function run()
    {
        // Fetch all categories to assign products to them
        $categories = Category::all();

        // Define the image links
        $imageLinks = [
            "http://localhost:8000/storage/product_img/1706547905.jpg",
            "http://localhost:8000/storage/product_img/1706547947.jpg",
            "http://localhost:8000/storage/product_img/1706547953.jpg",
            "http://localhost:8000/storage/product_img/1706547959.jpg",
            "http://localhost:8000/storage/product_img/1706547967.jpg",
            // "http://localhost:8000/storage/product_img/",
            // "http://localhost:8000/storage/product_img/",
            // "http://localhost:8000/storage/product_img/",
            // "http://localhost:8000/storage/product_img/",
            // "http://localhost:8000/storage/product_img/",
        ];

        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                Product::create([
                    'product_name' => 'Product ' . $i . ' - ' . $category->category_name,
                    'category_id' => $category->id,
                    'description' => 'Description for Product ' . $i,
                    'regular_price' => rand(2000, 10000),
                    'brand' => 'Brand ' . $i,
                    'product_img1' => $imageLinks[rand(0, count($imageLinks) - 1)],
                    'product_img2' => $imageLinks[rand(0, count($imageLinks) - 1)],
                    'product_img3' => $imageLinks[rand(0, count($imageLinks) - 1)],
                    'product_img4' => $imageLinks[rand(0, count($imageLinks) - 1)],
                    'product_img5' => $imageLinks[rand(0, count($imageLinks) - 1)],
                    'weight' => rand(1, 10),
                    'quantity_in_stock' => rand(10, 100),
                    'tags' => 'Tag' . $i,
                    'status' => 'active',
                    'sales_price' => rand(1000, 5000),
                    'meta_title' => 'Meta Title for Product ' . $i,
                    'meta_description' => 'Meta Description for Product ' . $i,
                    'cash_on_delivery' => rand(0, 1) ? 'true' : 'false',
                    'sku' => 'SKU' . $i,
                    'free_shipping' => rand(0, 1) ? 'true' : 'false',
                    'shipping_cost' => rand(0, 1) ? rand(5, 20) : null,
                    'tax' => rand(0, 1) ? rand(5, 15) : null,
                ]);
            }
        }
    }
}
