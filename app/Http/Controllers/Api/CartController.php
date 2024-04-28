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
use App\Http\Controllers\Api\OrderController;
use illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdated;

use App\Models\OrderItems;

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

        $cartItems = $user->carts()->join('products', 'carts.product_id', '=', 'products.id')->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')->get();

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
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ],
                    422,
                );
            }

            $product = Product::find($request->input('product_id'));

            if (!$product) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Product not found',
                    ],
                    404,
                );
            }

            // Check if the product is already in the user's cart
            $existingCartItem = $user
                ->carts()
                ->where('product_id', $product->id)
                ->first();

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
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                404,
            );
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
            $deleted = Cart::where('id', $cartId)->where('user_id', $userId)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cart item deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Cart item not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Error deleting cart item',
                    'error' => $e->getMessage(),
                ],
                500,
            );
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
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ],
                    422,
                );
            }

            $product = Product::find($request->input('product_id'));

            if (!$product) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Product not found',
                    ],
                    404,
                );
            }
            // Check if the product is already in the user's cart
            $existingCartItem = $user
                ->carts()
                ->where('product_id', $product->id)
                ->first();

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
                        return response()->json(
                            [
                                'status' => 'error',
                                'message' => 'Quantity exceeds available stock',
                            ],
                            422,
                        );
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
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function calculateShippingCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ],
                422,
            );
        }

        $address = $this->getAddress($request->address);

        $userAddress = $this->formatAddress($address);

        $shippingCost = $this->EstimatedShippingCharge($userAddress);

        return response()->json([
            'status' => 'success',
            'message' => 'Shipping cost calculated successfully',
            'data' => ['shipping_cost' => round($shippingCost,2)],
        ]);
    }

    public function EstimatedShippingCharge($userAddress)
    {
        $shippingCharge = 0;
        $totalItems = 0;
        $estimatedCostOnItems = 0;
        $costOnItems = 0.2; // 20 cents per item

        $user = Auth::user();
        $cartItems = $user->carts()->join('products', 'carts.product_id', '=', 'products.id')->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')->get();

        $totalItems = $cartItems->sum('quantity');

        if ($totalItems > 10) {
            $estimatedCostOnItems = $totalItems * $costOnItems;
        }

        $userCoordinates = $this->getCoordinatesFromAddress($userAddress);
        $adminSettings = AdminSettings::first();
        $adminAddress = $this->formatAdminAddress($adminSettings);
        $adminCoordinates = $this->getCoordinatesFromAddress($adminAddress);

        if ($userCoordinates && $adminCoordinates) {
            $distance = $this->vincentyGreatCircleDistance($userCoordinates, $adminCoordinates);

            // if distance is above 10km, calculate shipping charge
            if ($distance > 10) {
                $shippingCharge = round(($distance * $adminSettings->shipping_cost_per_meter) / 2, 2);
            } else {
                // free shipping
                $shippingCharge = 0;
            }
        }

        return $shippingCharge + $estimatedCostOnItems;
    }

    // Function to handle the checkout process
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|exists:addresses,id',
            'payment_method' => 'required|exists:payment_methods,id',
            // 'payment_method' => 'required|in:card,cash on delivery,bank transfer',
            'delivery_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ],
                422,
            );
        }

        $address = $this->getAddress($request->address);
        $shippingCharge = $this->EstimatedShippingCharge($address);
        $orderController = new OrderController();

        $user = Auth::user();
        $userId = $user->id;
        $tax = 0;
        try {
            $cartItems = $user->carts()->join('products', 'carts.product_id', '=', 'products.id')->select('carts.id as cart_id', 'carts.quantity', 'carts.user_id', 'products.*')->get();

            $totalCost = $cartItems->sum(function ($cartItem) {
                $tax = $cartItem->tax;
                return $cartItem->sales_price * $cartItem->quantity + $cartItem->tax;
            });

            $totalCost += $shippingCharge;
            $orderId = $orderController->generateOrderId();
            $note = $request->delivery_instructions;
            if (empty($note)) {
                $note = '';
            }

            $order = $user->orders()->create([
                'total_amount' => $totalCost,
                'shipping_charge' => $shippingCharge,
                'payment_method' => $request->payment_method,
                'notes' => $note,
                'status' => 'pending',
                'shipping_address' => $address->delivery_address,
                'billing_address' => $address->delivery_address,
                'email' => $user->email,
                'order_number' => $orderId,
                'user_id' => $userId,
                'payment_status' => 'pending',
                'delivery_status' => 'pending',
                'order_status' => 'pending',
                'tax' => $tax,
            ]);

            foreach ($cartItems as $cartItem) {
                $order->orderItems()->create([
                    'product_id' => $cartItem->id,
                    'price' => $cartItem->sales_price,

                    'quantity' => $cartItem->quantity,
                ]);
            }

            $user->carts()->delete();
            $order_user = User::where('id', $userId)->first();
            $recipientEmail = $order_user->email;
            $orderItemsContent = OrderItems::join('products', 'products.id', '=', 'order_items.product_id')
            ->where('order_items.order_number', $orderId)
            ->select('order_items.*', 'products.*')->get()->map(function ($item) {
                return (object) $item->toArray();
            });
            

            Mail::to($recipientEmail)->queue(new OrderStatusUpdated($order, $order_user, $orderItemsContent, $request->status));

        

            return response()->json([
                'status' => 'success',
                'message' => 'Checkout successful',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
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

    //use vincenty algorithm to calculate distance between two coordinates
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
        return $address['delivery_address'] . ', ' . $address['city'] . ', ' . $address['state'] . ', ' . $address['country'];
    }

    //function to format admin address
    public function formatAdminAddress($adminAddress)
    {
        return $adminAddress['office_address'];
    }
}
