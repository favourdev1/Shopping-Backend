<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\CartController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('get-user', [AuthController::class, 'userInfo']);
});


// Routes for non-logged-in users
Route::middleware(['shoppingGuest'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/discountproduct', [ProductController::class, 'getDiscountProduct']);
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/{productId}', [ProductController::class, 'show']);
        Route::get('/similarproduct/{productId}', [ProductController::class, 'similarProduct']);
    });


    Route::prefix('category')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
    });

});




// Authenticated routes
Route::middleware('auth:api')->group(function () {
    // user routes
    Route::prefix('profile')->group(function () {
        Route::get('/{user}', [UsersController::class, 'showProfile']);
        Route::put('/update', [UsersController::class, 'updateProfile']);
    });


    Route::prefix('carts')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add/{user}', [CartController::class, 'addOrUpdateCartItem']);
        Route::put('/update/{cart}', [CartController::class, 'update']);
        Route::delete('/delete/{cart}', [CartController::class, 'destroy']);
    });






    // Admin routes
    Route::prefix('admin')->middleware(['admin'])->group(function () {
        Route::get('/profile', [UsersController::class, 'GetAllUsers']);
        Route::put('/profile', [AdminController::class, 'updateProfile']);
        Route::get('/profileDetails/{user}', [UsersController::class, 'showProfile']);


        // admin-users functionalities
        Route::prefix('users')->group(function () {
            Route::post('/{user}/makeadmin', [AdminController::class, 'setAsAdmin']);
            Route::post('/{user}/removeadmin', [AdminController::class, 'disableAdmin']);
        });

        Route::prefix('category')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/{category}', [CategoryController::class, 'show']);
            Route::get('/create', [CategoryController::class, 'create']);
            Route::post('/add', [CategoryController::class, 'store']);
            Route::get('/{category}/edit', [CategoryController::class, 'edit']);
            Route::put('/update/{category}', [CategoryController::class, 'update']);
            Route::post('/upload-image', [CategoryController::class, 'upload']);
            Route::delete('/delete/{category}', [CategoryController::class, 'destroy']);

        });

        // Admin -> product Functionalities
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::post('/add', [ProductController::class, 'store']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::post('/upload-image', [ProductController::class, 'upload']);
            Route::delete('/delete/{product}', [ProductController::class, 'destroy']);
        });



    });




});