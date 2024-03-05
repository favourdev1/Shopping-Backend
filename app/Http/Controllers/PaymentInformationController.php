<?php

namespace App\Http\Controllers;

use App\Models\PaymentInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentInformationController extends Controller
{
    public function fetchPaymentInformation()
    {
        // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Fetch payment information for the authenticated user
        $paymentInformation = $user->paymentInformation()->get();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'data' => $paymentInformation,
        ]);
    }
}
