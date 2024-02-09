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
use Illuminate\Support\Facades\Http;


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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
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



    public function checkout(User $user)
    {
        try {
            // Get the user's cart items
            $cartItems = $user->carts()
                ->join('products', 'carts.product_id', '=', 'products.id')
                ->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')
                ->get();

            // Calculate the total cost including DHL shipping cost
            $totalCost = $this->calculateTotalCost($cartItems);

            // Create an order
            $order = $user->orders()->create([
                'total_amount' => $totalCost,
                // Add more order details as needed
            ]);

            // Move cart items to the order
            foreach ($cartItems as $cartItem) {
                $order->orderItems()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    // Add more item details as needed
                ]);
            }

            // Clear the user's cart
            $user->carts()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Checkout successful',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }





    private function calculateTotalCost($cartItems)
    {
        // Calculate the total cost based on product prices and quantities
        $subTotal = 0;
        foreach ($cartItems as $cartItem) {
            $subTotal += $cartItem->price * $cartItem->quantity;
        }

        // Call DHL API to get shipping cost
        $shippingCost = $this->getDHLShippingCost($cartItems);

        // Add the shipping cost to the total
        $totalCost = $subTotal + $shippingCost;

        return $totalCost;
    }

    private function getDHLShippingCost($cartItems)
    {
        // Replace 'your-api-key' with your actual DHL API key
        $apiKey = env('DHL_API_KEY');

        // DHL API endpoint for shipping rate calculation
        $apiEndpoint = 'https://api.dhl.com/ship/quote';

        // Prepare payload for the API request
        $payload = [
            'shipper' => [
                // Add shipper details
            ],
            'recipient' => [
                'address' => [
                    'countryCode' => 'US', // Adjust based on the recipient's country
                    // Add other address details
                ],
            ],
            'packages' => [],
        ];

        // Prepare package details based on cart items
        foreach ($cartItems as $cartItem) {
            $payload['packages'][] = [
                'weight' => $cartItem->weight,
                // Add other package details
            ];
        }

        // Make the API request
        $response = Http::withHeaders(['DHL-API-Key' => $apiKey])->post($apiEndpoint, $payload);

        // Decode the JSON response
        $apiResponse = $response->json();

        // You may need to handle errors and extract relevant information from the API response
        // In this example, assuming the API returns a 'shippingCost' key
        $shippingCost = $apiResponse['shippingCost'] ?? 0;

        return $shippingCost;
    }
}