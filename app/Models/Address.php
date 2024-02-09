<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable =[
        'firstname',
        'lastname',
        'city',
        'state',
        'country',
        'delivery_address',
        'postal_code',
        'phone_number_1',
        'phone_number_2',
    ];


    public function user (){
        return $this->belongsTo(User::class);
    }
}
