<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSettings extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'office_address',
        'shipping_cost_per_meter',
        'account_number_1',
        'account_number_2',
        'account_name_1',
        'account_name_2',
        'bank_name_1',
        'bank_name_2',
       
        
    ];

}
