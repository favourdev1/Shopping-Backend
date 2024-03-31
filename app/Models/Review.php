<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'heading',
        'description',
        'stars',
        'product_id',
        'description',
        'user_id'
    ];
}
