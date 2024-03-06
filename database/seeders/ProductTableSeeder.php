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
            "http://localhost:8000/storage/product_img/440x440.png_480x480.png_.webp",
            "http://localhost:8000/storage/product_img/computerImage .webp",
            "http://localhost:8000/storage/product_img/freezer.webp",
            "http://localhost:8000/storage/product_img/kitchen2.webp",
            "http://localhost:8000/storage/product_img/kitchenset.webp",
            "http://localhost:8000/storage/product_img/security.webp",
            "http://localhost:8000/storage/product_img/product_img/Mini-Fridge-7-5L-Car-Home-Refrigerator-Mini-Fridges-12V-Freezer-Cooler-Heater-Food-Storage-Box.jpg_350x350xz.jpg_.webp",
            "http://localhost:8000/storage/product_img/Se6771ae3e68b46978cbb92f7628a9d18D.jpg",
            "http://localhost:8000/storage/product_img/S61aef97c473d459e9475a7fd3db9dba9d.jpg",
            "http://localhost:8000/storage/product_img/S4395d532050e417f93e76b141592931bL",
            "http://localhost:8000/storage/product_img/S4967cf175e9f485ab82c0c07e1b331bda.jpg",
            "http://localhost:8000/storage/product_img/S9489ca8eb56c4246b5a4d92c464e6f11N.jpg",
            "http://localhost:8000/storage/product_img/Sa54692a9361f41e99fd60cdf91efc99bX.jpg",
            "http://localhost:8000/storage/product_img/Sad96b653909c4217be9da0e43d7e1dcfS.jpg",
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
