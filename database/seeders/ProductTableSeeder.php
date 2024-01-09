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
            "https://ae01.alicdn.com/kf/Sf8e138757c0646538f35aa037db3438ei/Air-Conditioner-Mini-Fan-Cooler-Portable-Air-Cooler-AC-Air-Conditioning-3-Gear-Speed-Air-Cooling.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/S355a08bee5e14ba68ff955f587b3b78fy/Air-Conditioner-Mini-Fan-Cooler-Portable-Air-Cooler-AC-Air-Conditioning-3-Gear-Speed-Air-Cooling.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/Sa24f41ac53234b6ea52a15062a3c2517J/Air-Conditioner-Mini-Fan-Cooler-Portable-Air-Cooler-AC-Air-Conditioning-3-Gear-Speed-Air-Cooling.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/S6c06f3c1a193440ca0eff17a4485b86bh/Air-Conditioner-Mini-Fan-Cooler-Portable-Air-Cooler-AC-Air-Conditioning-3-Gear-Speed-Air-Cooling.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/S40031f52a8184193b988b4b6752ce230a/Air-Conditioner-Mini-Fan-Cooler-Portable-Air-Cooler-AC-Air-Conditioning-3-Gear-Speed-Air-Cooling.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/Sb11840c270364af4a8e443327c2e90e5Z/Air-Conditioner-Mini-Fan-Cooler-Portable-Air-Cooler-AC-Air-Conditioning-3-Gear-Speed-Air-Cooling.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/Seb396371f0814b66b628a6d01030bce6r/58L-0-38kw-24h-Household-Small-Commercial-Large-Capacity-Refrigeration-Horizontal-Freezer-Rapid-Freezing-Rental-Small.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/Sc11aff73450d49bb89066ec4d302e469r/58L-0-38kw-24h-Household-Small-Commercial-Large-Capacity-Refrigeration-Horizontal-Freezer-Rapid-Freezing-Rental-Small.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/S9720f19e45d44bf1a0ed35c90fa130aaZ/58L-0-38kw-24h-Household-Small-Commercial-Large-Capacity-Refrigeration-Horizontal-Freezer-Rapid-Freezing-Rental-Small.jpg_350x350xz.jpg_.webp",
            "https://ae01.alicdn.com/kf/Sa8e2c390427f4f30aae4d1a198c233c9j/58L-0-38kw-24h-Household-Small-Commercial-Large-Capacity-Refrigeration-Horizontal-Freezer-Rapid-Freezing-Rental-Small.jpg_350x350xz.jpg_.webp",
        ];

        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                Product::create([
                    'product_name' => 'Product ' . $i . ' - ' . $category->category_name,
                    'category_id' => $category->id,
                    'description' => 'Description for Product ' . $i,
                    'regular_price' => rand(50, 200),
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
                    'sales_price' => rand(30, 150),
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
