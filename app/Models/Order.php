<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_amount',
        'tax',
        'order_number',
        'order_status',
        'payment_status',
        'shipping_charge',
        'delivery_status',
        'status',
        'shipping_address',
        'payment_method',
        'billing_address',
        'email',
        'notes',
    ];


    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }
}
