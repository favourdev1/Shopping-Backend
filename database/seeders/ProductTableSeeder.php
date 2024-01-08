<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Define the number of dummy products you want to create
       $numberOfProducts = 10;

       // Create dummy products
       \App\Models\Product::factory($numberOfProducts)->create();
    }
}
