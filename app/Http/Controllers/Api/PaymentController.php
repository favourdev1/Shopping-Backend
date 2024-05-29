<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

use App\Models\PaymentMethod;

class PaymentController extends Controller
{
    public function index()
    {
        $payment = Payment::all();
        return response()->json([
            'status' => 'success',
            'data' => $payment,
        ]);
    }



    // function to store the payment infomation in the database
    public function store(Request $request)
    {
        $payment_method = 1; // default payment method

        $request->validate([
            'order_number' => 'required',
            'payment_proof' => 'required|image',
            'payment_amount' => 'required',
            'account_number' => 'required',
        ]);

        $order = Order::where('order_number', $request->order_number)->first();
        $orderid = $order->id;

        $user = auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'user not authenticated',
            ]);
        }

        $user_id = $user->id;
        $image = $request->file('payment_proof');
        $imageName = time() . '.' . $image->extension();
        $image->move(public_path('images'), $imageName);

        $payment = Payment::create([
            'order_id' => $orderid,
            'account_number' => $request->account_number,
            'user_id' => $user_id,

            'payment_status' => 'completed',
            'payment_amount' => $request->payment_amount,
            'image' => $imageName,
            'payment_date' => \Date::now(),
            'approval_status' => 'pending',
        ]);
        $order = Order::where('order_number', $request->order_number)->first();

        if ($order && $order->payment) {
            $order->payment->payment_status = 'completed';
            $order->payment->save();
        } else {
            // Handle the case where the order or payment was not found
            return response()->json([
                'status' => 'error',
                'message' => '.An internal error occured!.Order not found',
            ]);
        }

        return response()->json(
            [
                'status' => 'success',
                'message' => 'payment successfully made',
                'data' => $payment,
            ],
            201,
        );
    }

    // function to generate orderId
    public function generateOrderId()
    {
        // function to generate unique combination of letters and numbers
        $orderId = 'ORD' . time() . $this->generateUniqueCode(5);
        return response()->json([
            'status' => 'success',
            'data' => ['order_id' => $orderId],
        ]);
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
}
