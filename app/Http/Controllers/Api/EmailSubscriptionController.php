<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\EmailSubscription;
use App\Http\Controllers\Controller;

class EmailSubscriptionController extends Controller
{

    public function index(){
        return EmailSubscription::all();
    
    }

    
    public function store(Request $request)
    {

      
        
        $request->validate([
            'email' => 'required|email|unique:email_subscriptions,email',
            'tag'=>'sometimes|string'
        ]);

        $subscription = EmailSubscription::firstOrCreate(['email' => $request->email]);

        return response()->json($subscription, 201);
    }
}
