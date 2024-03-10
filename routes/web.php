<?php

use Illuminate\Support\Facades\Route;
use App\Mail\OrderShipped;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



// Email ROutes 
Route::get('/mailable', function () {
    $order = Order::find(5); // Get an order from the database
    return new OrderShipped($order); // Return an instance of the mailable
});