<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminSettings;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use illuminate\Http\facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;

// use illuminate\Support\Facades\DB;
class OrderController extends Controller
{
    public function generateOrderId()
    {
        // function to generate unique combination of letters and numbers
        do {
            $orderId = 'ORD' . time() . self::generateUniqueCode(5);
            $orders = Order::where('order_number', $orderId)->first();
        } while ($orders);

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

    public function adminFetchOrders()
    {
        $user = auth()->user();
        $orders = [];
        if ($user) {
            if ($user->is_admin == true) {
                $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
                    ->join('payment_methods', 'orders.payment_method', 'payment_methods.id')->select('orders.*', 'payment_methods.name as payment_method', 'users.firstname', 'users.lastname', 'orders.id as id', 'orders.status as order_status')->orderBy('created_at', 'desc')->paginate(10);
            } else {
                return response()->json(['message' => 'User not authenticated'], 401);
            }
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

    // fetch all the orders for a particular user
    public function fetchOrders()
    {
        $user = auth()->user();
        $orders = [];
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }


        $orders = $user->orders()->join('order_items', 'orders.id', '=', 'order_items.order_id')->join('products', 'products.id', '=', 'order_items.product_id')->join('users', 'orders.user_id', '=', 'users.id')->select('orders.*', 'products.*', 'products.id as product_id', 'order_items.quantity as quantity', 'users.firstname', 'users.lastname', 'orders.id as id', 'order_items.order_id as order_items_id', 'orders.status as order_status')->get();

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

        $order = $user
            ->orders()
            ->where('order_number', $order_number)

            ->first();

        $order_id = $order->id;

        $orderItems = $user->orders()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('reviews', 'reviews.product_id', '=', 'products.id')
            ->where('order_items.order_id', $order_id)
            // ...

            ->select(
                'orders.*',
                'order_items.*',
                'products.*',
                DB::raw('(CASE WHEN reviews.id IS NOT NULL THEN TRUE ELSE FALSE END) as review_exists')
            )
            ->get();


        $paymentInfomation = Payment::where('order_id', $order_id)->first();
        $adminSettings = AdminSettings::first();
        if (!$order) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Order not found order number = ' . $order_number,
                ],
                200,
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order fetched successfully',
            'data' => [
                'admin_settings' => $adminSettings,
                'order' => $order,
                'order_items' => $orderItems,
                'payment_info' => $paymentInfomation,
            ],
        ]);
    }

    public function adminFetchOrderbyOrderNumber($order_number)
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

        $order = $user->orders()->where('order_number', $order_number)->orderBy('created_at', 'desc')->first();

        $order_id = $order->id;
        $user_id = $order->user_id;

        $user_info = User::where('id', $user_id)->first();

        $orderItems = OrderItems::join('products', 'products.id', '=', 'order_items.product_id')->where('order_items.order_id', $order_id)->get();

        $paymentInfomation = Payment::where('order_id', $order_id)->first();
        $adminSettings = AdminSettings::first();
        if (!$order) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Order not found order number = ' . $order_number,
                ],
                200,
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order fetched successfully',
            'data' => [
                'admin_settings' => $adminSettings,
                'order' => $order,
                'user_info' => $user_info,
                'order_items' => $orderItems,
                'payment_info' => $paymentInfomation,
            ],
        ]);
    }

    public function updateOrderStatus(Request $request)
    {
        //write me a list of order status
        // pending, processing, shipped, delivered, cancelled
        $orderStatus = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'order_number' => 'required',
            'status' => 'required | in:pending,processing,shipped,delivered,cancelled',

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

        //check the previous statuss  that is on the database
        //if the status is the same as the new status, return an error message
        //if the status is different, update the status
        //return a success message

        $order = Order::where('order_number', $request->order_number)->first();

        if (!$order) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Order not found',
                ],
                200,
            );
        }
        $order_user = User::where('id', $order->user_id)->first();
        $recipientEmail = $order_user->email;
        // $orderItemsContent = OrderItems::join('products', 'products.id', '=', 'order_items.product_id')
        // ->where('order_items.order_id', $order->id)
        // ->select('order_items.*', 'products.*')->get();
        $orderItemsContent = OrderItems::join('products', 'products.id', '=', 'order_items.product_id')
        ->where('order_items.order_id', $order->id)
        ->select('order_items.*', 'products.*')->get()->map(function ($item) {
            return (object) $item->toArray();
        });
        if ($order->status == $request->status) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Order status is already ' . $request->status,
                ],
                200,
            );
        }

        if ($order->status == 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order has been already cancelled, you cannot update the status',
            ]);
        }
        if ($order->status == 'delivered') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order has been already delivered, you cannot update the status',
            ]);
        }
        // $order->update(
        //     [
        //         'status' => $request->status,
        //         'delivery_status' => $request->status
        //     ]
        // );

        // send email to the user
        // find out why producct name is not being added in orderitems content  array 


        // Mail::to($recipientEmail)->send(new OrderStatusUpdated($order, $order_user, $orderItemsContent, $request->status));

        Mail::to($recipientEmail)->queue(new OrderStatusUpdated($order, $order_user, $orderItemsContent, $request->status));

        
        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data'=>$orderItemsContent
        ]);
    }

    public function test (){
      echo "<pre>";
      print_r( User::where('id', 1)->select('firstname', 'lastname', 'email')->first());
    }
}