<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminSettings;
use App\Models\Order;
use Illuminate\Http\Request;
use illuminate\Http\facades\Auth;
use Illuminate\Support\Facades\Validator;


class OrderController extends Controller
{
    public function generateOrderId()
    {
        // function to generate unique combination of letters and numbers
        do {
            $orderId = 'ORD' . time() . self::generateUniqueCode(5);
            $orders = Order::where('order_number',$orderId)->first();
        } while($orders);
    
        return $orderId;
    }

    public function generateUniqueCode($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $code .= $characters[$index];
        }
        return $code;
    }

    // fetch all the orders for a particular user
    public function fetchOrders()
    {
        $user = auth()->user();

        $orders = [];
        if ($user) {
            $orders = $user->orders()->join('order_items', 'orders.id', '=', 'order_items.order_id')->join('products', 'products.id', '=', 'order_items.product_id')->get();
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        return response()->json(
            [
                'data' => $orders,
                'message' => 'Orders fetched successfully.',
                'status' => 'success',
            ],
            200,
        );
    }

    public function fetchOrderbyOrderNumber($order_number)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        // run validation
        $validator = Validator::make(
            ['order_number' => $order_number],
            [
                'order_number' => 'required',
            ],
        );

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


        $order = $user->orders()->where('order_number', $order_number)->first();
        $adminSettings = AdminSettings::first();
        if (!$order) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Order not found order number = '.$order_number,
                ],
                200,
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order fetched successfully',
            'data' => ['admin_settings'=>$adminSettings,'order'=>$order]
        ]);
    }
}
