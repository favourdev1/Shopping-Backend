<?php

namespace App\Http\Controllers\Api;

use App\Mail\EmailSubscribed;
use Illuminate\Http\Request;
use App\Models\EmailSubscription;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Mail;
use App\Mail\OrderShipped;
use App\Models\Order;

class EmailSubscriptionController extends Controller
{
    public function index()
    {
        $order = Order::find(1);
        //         Mail::to('favourapps17@gmail.com')->send(new OrderShipped($order));
        //         $emailContent = (new OrderShipped($order))->render();
        // return $emailContent;
        // mail::to('')
        // $emailContent = (new  EmailSubscribed('favour@gmail.com'))->render();
        // return $emailContent;
        return response()->json(['status' => 'success'], 201);
        // return EmailSubscription::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:email_subscriptions,email',
            'tag' => 'sometimes|string',
        ]);

        $subscription = EmailSubscription::firstOrCreate(['email' => $request->email]);

        Mail::to($request->email)->send(new EmailSubscribed($request->email));
        //         $emailContent = (new  EmailSubscribed($request->email))->render();
        // return $emailContent;
        return response()->json($subscription, 201);
    }
}
