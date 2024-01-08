<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function index()
    {
        $user = Auth::user();
    }



    public function addOrDelete(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::find($request->input('product_id'));

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            // Check if the product is already in the user's wishList
            $existingwishListItem = $user->wishLists()->where('product_id', $product->id)->first();
            if ($existingwishListItem) {
                $deleteWishlist = $existingwishListItem->delete();
                if ($deleteWishlist) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'wishlist deleted successfully'
                    ]);
                }
            }


            // If the product is not in the wishList, create a new wishList item
            $wishListItem = $user->wishLists()->create([
                'product_id' => $product->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product added to wishList successfully',
                'data' => $wishListItem,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

}
