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
use App\Models\Address;
use App\Models\AdminSettings;

class CartController extends Controller
{

    // ensure that user is authenticated before accesing this 
    public function __construct()
    {
        $this->middleware('auth:api');
    }
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



    public function calculateShippingCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $address = $this->getAddress($request->address);

        $userAddress = $this->formatAddress($address);

        $shippingCost = $this->EstimatedShippingCharge($userAddress);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Shipping cost calculated successfully',
            'data' => ['shipping_cost'=>$shippingCost],
        ]);

    }

    
    public function EstimatedShippingCharge($userAddress)
    {

        $shippingCharge = 0;
        $totalItems = 0;
        $estimatedCostOnItems = 0;
        $costOnItems = 0.2; // 20 cents per item

        $user = Auth::user();
        $cartItems = $user->carts()
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')
            ->get();

        $totalItems = $cartItems->sum('quantity');

        if ($totalItems > 3) {
            $estimatedCostOnItems = $totalItems * $costOnItems;
        }

        $userCoordinates = $this->getCoordinatesFromAddress($userAddress);
        $adminSettings = AdminSettings::first();
        $adminAddress= $this->formatAdminAddress($adminSettings);
        $adminCoordinates = $this->getCoordinatesFromAddress($adminAddress);

       
        if ($userCoordinates && $adminCoordinates) {
            $distance = $this->vincentyGreatCircleDistance($userCoordinates, $adminCoordinates);


            // if distance is above 10km, calculate shipping charge
            if ($distance > 10) {
                $shippingCharge =round(($distance * $adminSettings->shipping_cost_per_meter)/2,2);
            }else{
                // free shipping 
                $shippingCharge = 0;
            }
        }

        return $shippingCharge + $estimatedCostOnItems;
    }


    // Function to handle the checkout process
    public function checkout(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'address' => 'required|exists:addresses,id', // Address must exist in the addresses table
            'payment_method' => 'required|in:card,transfer', // Payment method must be either 'card' or 'transfer'
            'delivery_instructions' => 'nullable|string', // Delivery instructions, if provided, must be a string
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Calculate the shipping charge based on the selected address
        $address = $this->getAddress($request->address);
        $shippingCharge = $this->EstimatedShippingCharge($address);

        $user = Auth::user();

        try {
            // Get the user's cart items
            $cartItems = $user->carts()
                ->join('products', 'carts.product_id', '=', 'products.id')
                ->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')
                ->get();

            // Calculate the total cost including shipping charge
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
        
    
    public function getAddress($addressId)
    {
        return Address::find($addressId)->first();
    }







    // Function to get coordinates from an address using LocationIQ API
    function getCoordinatesFromAddress($formattedAddress)
    {
        // Get the Geoapify API key from the environment variables

        $apiKey = env('GEOAPIFY_API_KEY');

        // Concatenate the delivery address, city, and state

        // Encode the address for use in the API request
        $encodedAddress = urlencode($formattedAddress);


        // Construct the API URL
        $apiUrl = "https://api.geoapify.com/v1/geocode/search?text={$encodedAddress}&apiKey={$apiKey}";

        // Make a GET request to the API
        $response = Http::get($apiUrl);

        // Decode the JSON response
        $data = $response->json();

        // Check if response contains any results
        if (is_array($data) && !empty($data['features']) && isset($data['features'][0]['geometry']['coordinates'])) {
            // Extract latitude and longitude from the first result
            $latitude = $data['features'][0]['geometry']['coordinates'][1];
            $longitude = $data['features'][0]['geometry']['coordinates'][0];


            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        } else {
            // No results found
            return [];
        }
    }

    
    //use vincenty algorithm to calculate distance
    // Function to calculate distance using Vincenty formula
    function vincentyGreatCircleDistance($userCoordinates, $adminCoordinates)
    {
        $lat1 = deg2rad($userCoordinates['latitude']);
        $lon1 = deg2rad($userCoordinates['longitude']);
        $lat2 = deg2rad($adminCoordinates['latitude']);
        $lon2 = deg2rad($adminCoordinates['longitude']);

        // radius in kilometers
        $earthRadius = 6371;

        $deltaLon = $lon2 - $lon1;

        $numerator = sqrt(pow(cos($lat2) * sin($deltaLon), 2) + pow(cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($deltaLon), 2));
        $denominator = sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($deltaLon);

        $angle = atan2($numerator, $denominator);

        $distance = $earthRadius * $angle;

        return $distance;
    }


    // function to format user address 
    public function formatAddress($address)
    {
        return $address['delivery_address'] . ', ' . $address['city'] . ', ' . $address['state'] . ', ' . $address['country'] ;
    }

    //function to format admin address
    public function formatAdminAddress($adminAddress)
    {
        return $adminAddress['office_address'];
    }


}