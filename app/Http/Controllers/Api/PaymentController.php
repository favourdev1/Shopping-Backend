<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

use App\Models\PaymentMethod;

class PaymentController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return response()->json([
            'status' => 'success',
            'data' => $paymentMethods
        ]);

    }

    // function to store the payment infomation in the database
    public function store(Request $request)
    {
      $payment_method = "bank transfer"; // default payment method 

        $request->validate([
            'order_id' => 'required|integer',
           'payment_proof' => 'required|image',
        ]);

        $image = $request->file('image');
        $imageName = time() . '.' . $image->extension();
        $image->move(public_path('images'), $imageName);

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'user_id' => $request->user_id,
            'payment_method_id' => $request->payment_method_id,
            'payment_status' => $request->payment_status,
            'payment_amount' => $request->payment_amount,
            'image' => $imageName,
            'payment_date' => $request->payment_date,
            'approval_status' => $request->approval_status
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ], 201);
    }

    // function to generate orderId
    public function generateOrderId()
    {
        // function to generate unique combination of letters and numbers
        $orderId = 'ORD' . time() . $this->generateUniqueCode(
            5
        );
        return response()->json([
            'status' => 'success',
            'data' => ['order_id' => $orderId]
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
