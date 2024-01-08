<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'category_id',
        'description',
        'regular_price',
        'brand',
        'product_img1',
        'product_img2',
        'product_img3',
        'product_img4',
        'product_img5',
        'weight',
        'quantity_in_stock',
        'tags',
        'refundable',
        'status',
        'sales_price',
        'meta_title',
        'meta_description',
        'free_shipping',
        'cash_on_delivery,',
        'sku',
        'shipping_cost',
        'tax'
    ];

}
