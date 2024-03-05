<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'user_id',
        'payment_method_id',
        'payment_status',
        'payment_amount',
        'image',
        'payment_date',
        'approval_status'



    ];
}
