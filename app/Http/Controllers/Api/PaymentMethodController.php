<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
class PaymentMethodController extends Controller
{
    

    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return response()->json([
            'status' => 'success',
            'data' =>['paymentMethods'=>$paymentMethods]
        ]);



    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $paymentMethod
        ], 201);
    }

    public function delete($id)
    {
        $paymentMethod = PaymentMethod::find($id);
        if ($paymentMethod) {
            $paymentMethod->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Payment method deleted successfully.'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment method not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $paymentMethod = PaymentMethod::find($id);
        if ($paymentMethod) {
            $paymentMethod->name = $request->name;
            $paymentMethod->save();
            return response()->json([
                'status' => 'success',
                'data' => $paymentMethod
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment method not found.'
            ], 404);
        }
    }

}
