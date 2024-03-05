<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminSettings;

class AdminSettingsTableSeeder extends Seeder
{
 
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminSettings::firstOrCreate(
            [], 
            [
                'office_address' => '123 Main St, Anytown, USA',
                'shipping_cost_per_meter' => 5.00
            ]
        );
    }
}