<?php


namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;



class CartController extends Controller
{
  public function index()
{
    $user = Auth::user();

    $cartItems = $user->carts()
    ->join('products', 'carts.product_id', '=', 'products.id')
    ->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')
    ->get();

    return response()->json([
        'status' => 'success',
        'data' => $cartItems,
    ]);
}

    public function store(Request $request, User $user)
    {
        try{
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
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
    
        // Check if the product is already in the user's cart
        $existingCartItem = $user->carts()->where('product_id', $product->id)->first();
    
        if ($existingCartItem) {
            // If the product is already in the cart, update the quantity
            $existingCartItem->update([
                'quantity' => $existingCartItem->quantity + $request->input('quantity'),
            ]);
    
            return response()->json([
                'status' => 'success',
                'message' => 'Cart item Already Exists',
                'data' => $existingCartItem,
            ]);
        }
    
        // If the product is not in the cart, create a new cart item
        $cartItem = $user->carts()->create([
            'product_id' => $product->id,
            'quantity' => $request->input('quantity'),
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully',
            'data' => $cartItem,
        ]);}
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' =>$e->getMessage(),
            ], 404);  
        }
    }
    
    
    

    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update([
            'quantity' => $request->input('quantity'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item updated successfully',
            'data' => $cart,
        ]);
    }

    public function destroy($cartId)
    {

        try {
            $cart = Cart::findOrFail($cartId);
            $cart->delete();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Cart item deleted successfully',
            ]);
    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting cart item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    



    public function addOrUpdateCartItem(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
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

            // Check if the product is already in the user's cart
            $existingCartItem = $user->carts()->where('product_id', $product->id)->first();

            if ($existingCartItem) {
                // If the product is already in the cart, update the quantity
                $newQuantity = $existingCartItem->quantity + $request->input('quantity');

                // Ensure the updated quantity is not more than the quantity_in_stock
                if ($newQuantity > $product->quantity_in_stock) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Quantity exceeds available stock',
                    ], 422);
                }

                $existingCartItem->update([
                    'quantity' => $newQuantity,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cart item updated successfully',
                    'data' => $existingCartItem,
                ]);
            }

            // If the product is not in the cart, create a new cart item
            $cartItem = $user->carts()->create([
                'product_id' => $product->id,
                'quantity' => $request->input('quantity'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product added to cart successfully',
                'data' => $cartItem,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
