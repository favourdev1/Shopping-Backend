<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
          // Define an array of payment methods
    $paymentMethods = [
        'Credit Card',
        'Debit Card',
        'PayPal',
        'Pay on Delivery',
        'Bank Transfer',
        // Add more payment methods as needed
    ];

    // Loop through the payment methods
    foreach ($paymentMethods as $method) {
        // Create a new record for each payment method
        PaymentMethod::create(['name' => $method]);
    }

    }
}
