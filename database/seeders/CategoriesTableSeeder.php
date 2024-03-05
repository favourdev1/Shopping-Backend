<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            ['category_name' => 'Laundry', 'description' => 'Description for Laundry', 'status' => 'active','slug'=>'noslug1'],
            ['category_name' => 'Dishwashers', 'description' => 'Description for Dishwashers', 'status' => 'active','slug'=>'no slug1'],
            ['category_name' => 'Fridges & Freezers', 'description' => 'Description for Fridges & Freezers', 'status' => 'active','slug'=>'no slug2'],
            ['category_name' => 'Cooking', 'description' => 'Description for Cooking', 'status' => 'active','slug'=>'no slug3'],
            ['category_name' => 'Small Appliances', 'description' => 'Description for Small Appliances', 'status' => 'active','slug'=>'no slug4'],
            ['category_name' => 'Garden & DIY', 'description' => 'Description for Garden & DIY', 'status' => 'active','slug'=>'no slug5'],
            ['category_name' => 'Health & Beauty', 'description' => 'Description for Health & Beauty', 'status' => 'active','slug'=>'no slug6'],
        ];

        // Insert categories into the database
        DB::table('categories')->insert($categories);
    }
}
