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
                'shipping_cost_per_meter' => 5.00,
                'account_number_1' => '0892128066',
                'account_name_1' => 'Jachike Onuigbo',
                'account_number_2' => '0987654321',
                'account_name_2' => 'Jachike Onuigbo',
                'bank_name_1' => 'Orange Money',
                'bank_name_2' => 'Mpesa',

            ]
        );
    }
}