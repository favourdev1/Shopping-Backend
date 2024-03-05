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
        'other_variable_1',
        'other_variable_2',
        
    ];

}
