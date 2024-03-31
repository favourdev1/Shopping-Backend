<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 reviews
        for ($i = 0; $i < 10; $i++) {
            \App\Models\Review::factory()->create();
        }
    }
}
