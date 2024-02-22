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

    public function destroy($cartId, $userId)
    {

        try {
            $deleted = Cart::where('id', $cartId)
                ->where('user_id', $userId)
                ->delete();

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
                'quantity' => 'required|numeric|min:0',
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

                $cartId = $existingCartItem->id;
                $userId = $existingCartItem->user_id;
                if ($request->input('quantity') == '0') {

                    $this->destroy($cartId, $userId);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Cart item deleted successfully',
                        'data' => [],
                    ]);
                } else {

                    // If the product is already in the cart, update the quantity
                    // $newQuantity = $existingCartItem->quantity + $request->input('quantity');
                    $newQuantity = $request->input('quantity');

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



    public function checkout(Request $request)
    {
        #validate the request
        $request->validate([
            #adddess has to be in the address table 
            'address' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:card,transfer',
            'delivery_instructions' => 'nullable|string',
        ]);

        
        $user = Auth::user();

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









    // Function to calculate distance using Vincenty formula
    function vincentyGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        // Convert latitude and longitude from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        // Calculate differences
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        // Vincenty formula for distance
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        // Distance calculation
        $distance = $angle * $earthRadius;

        return $distance;
    }

    // Function to get latitude and longitude from address using OpenStreetMap Nominatim API
    function getCoordinatesFromAddress($address)
    {
        // Encode the address for use in URL
        $encodedAddress = urlencode($address);

        // Construct the API URL
        $apiUrl = "https://nominatim.openstreetmap.org/search?q={$encodedAddress}&format=json";

        // Make a GET request to the API
        $response = file_get_contents($apiUrl);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Check if response contains any results
        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            // Extract latitude and longitude from the first result
            $latitude = $data[0]['lat'];
            $longitude = $data[0]['lon'];
            return [$latitude, $longitude];
        } else {
            // No results found
            return null;
        }
    }



}