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
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderController;
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
Route::post('logout', [AuthController::class, 'logout']);

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

    Route::prefix('group')->group(function () {
        Route::get('/newArrivial', [ProductController::class, 'getNewArrivalsAutomatically']);
    });
});

// ==============================================
// =========== Authenticated routes =====================
// ==============================================
Route::middleware('auth:api')->group(function () {
    // user routes
    Route::prefix('profile')->group(function () {
        Route::get('/{user}', [UsersController::class, 'showProfile']);
        Route::put('/update', [UsersController::class, 'updateProfile']);
    });
    
    // Order routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'fetchOrders']);
        Route::get('/fetch/{order_number}',[OrderController::class, 'fetchOrderbyOrderNumber']);
    });

    Route::get('/generate-order-id', [PaymentController::class, 'generateOrderId']);

    Route::prefix('payments')->group(function () {
        // Route::get('/payment-methods', [PaymentController::class, 'index']);
        Route::post('/add', [PaymentController::class, 'store']);
    });

    // Wishlist routes
    Route::prefix('wishlists')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/add/', [WishlistController::class, 'addOrDelete']);
    });


    // Cart routes
    Route::prefix('carts')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add/{user}', [CartController::class, 'addOrUpdateCartItem']);
        Route::delete('/delete/{cart}/{userId}', [CartController::class, 'destroy']);
        Route::post('/checkout', [CartController::class, 'checkout']);
        Route::post('/shippingCost', [CartController::class, 'calculateShippingCost']);
    });

    // Address routes
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/add/{user}', [AddressController::class, 'store']);
        Route::put('/update', [AddressController::class, 'update']);
        Route::get('/setDefault/{addressId}', [AddressController::class, 'setDefaultddress']);
        Route::delete('/delete/{address}', [AddressController::class, 'destroy']);
    });




    // ============================== ==========================
    // ======================== Admin routes ====================================
    // ============================== ==========================
    Route::prefix('admin')
        ->middleware(['admin'])
        ->group(function () {
            Route::get('/profile', [UsersController::class, 'GetAllUsers']);
            Route::put('/profile', [AdminController::class, 'updateProfile']);
            Route::get('/profileDetails/{user}', [UsersController::class, 'showProfile']);

            // admin-users functionalities
            Route::prefix('users')->group(function () {
                Route::post('/{user}/makeadmin', [AdminController::class, 'setAsAdmin']);
                Route::post('/{user}/removeadmin', [AdminController::class, 'disableAdmin']);
            });


            // Payment controller functionalities 
            Route::prefix('payments')->group(function () {
                Route::get('/', [PaymentController::class, 'index']);
            });

            // Category Functionalities
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

            Route::prefix('settings')->group(function () {
                Route::get('/admin-settings', [AdminController::class, 'getAdminSettings']);
                Route::put('/admin-settings', [AdminController::class, 'updateAdminSettings']);
            });
        });
});
