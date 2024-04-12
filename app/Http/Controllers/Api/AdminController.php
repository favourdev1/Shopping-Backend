<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\AdminSettings;
use App\Models\Order;
use App\Models\Review;

use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api'); // Apply 'auth' middleware to all methods in this controller
    }

    /**
     * Set the user as an admin.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAsAdmin(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ]);
        }
        if(!$user->is_admin){
            return response()->json([
                'status' => 'error',
                'message' => 'Operation not allowed!',
            ]);
        }
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $userId = $request->input('user_id');
        $adminUser = User::find($userId);
        if (!$adminUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin user not found',
            ]);
        }

        $adminUser->update(['is_admin' => true]);

       return response()->json([
        'status' => 'success',
        'message' => 'Admin status updated successfully',
       ]);
    }

    /**
     * Disable admin status for the user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function disableAdmin(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ]);
        }
        if(!$user->is_admin){
            return response()->json([
                'status' => 'error',
                'message' => 'Operation not allowed!',
            ]);
        }
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $userId = $request->input('user_id');
        $adminUser = User::find($userId);
        if (!$adminUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin user not found',
            ]);
        }

        $adminUser->update(['is_admin' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin status is now removed for this user',
        ]);
    }

    /**
     * Generate a success JSON response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse(string $message): JsonResponse
    {
        return response()->json(
            [
                'status' => 'success',
                'message' => $message,
            ],
            200,
        );
    }

    public function getAdminSettings()
    {
        $adminSettings = AdminSettings::first();
        return response()->json([
            'status' => 'success',
            'data' => ['admin_settings' => $adminSettings],
        ]);
    }

    public function updateAdminSettings(Request $request, AdminSettings $adminSettings)
    {
        $request->validate([
            'office_address' => 'sometimes|string',
            'shipping_cost_per_meter' => 'sometimes|string',
            'account_number_1' => 'sometimes|string',
            'account_number_2' => 'sometimes|string',
            'account_name_1' => 'sometimes|string',
            'account_name_2' => 'sometimes|string',
            'bank_name_1' => 'sometimes|string',
            'bank_name_2' => 'sometimes|string',
        ]);
        $firstAdminSetting = AdminSettings::first();
        if ($firstAdminSetting) {
            $firstAdminSetting->update($request->all());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Admin settings updated successfully',
        ]);
    }

    public function dashboardInfo()
    {
        $allOrders = Order::all();
        $totalOrders = $allOrders->count();
        $allCustomers = User::where('is_admin', false)->get();
        $totalCustomers = $allCustomers->count();
        $totalRevenue = $allOrders->sum('total_amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_orders' => $totalOrders,
                'total_customers' => $totalCustomers,
                'total_revenue' => $totalRevenue,
            ],
        ]);
    }

    public function showReviews()
    {
        $review = Review::get();
        return response()->json([
            'status' => 'success',
            'message' => 'review fetched successfully',
            'data' => [
                'review' => $review,
            ],
        ]);
    }

    public function getAllPayments()
    {
        $user = auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'user not authenticated',
            ]);
        }

        if (!$user->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operation not allowed!',
            ]);
        }

        $allPayments = Payment::join('orders', 'orders.id', '=', 'payments.order_id')
        ->join('users', 'users.id', '=', 'orders.user_id')
        ->join('payment_methods', 'payment_methods.id', '=', 'orders.payment_method')
        ->select('orders.order_number',
                'payment_methods.name as payment_method',
                'orders.payment_method as payment_method_id',
                'payments.*',
                'users.firstname',
                'users.lastname')
                ->orderBy('payments.created_at', 'desc')
                ->paginate();
        return response()->json([
            'status' => 'success',
            'message' => 'payments fetched successfully ',
            'data' => [
                'payments' => $allPayments,
            ],
        ]);



    }


    public function updatePaymentStatus(Request $request){
        $request->validate([
            'approval_status'=>'required',
            'payment_id'=>'required',

        ]);
        

        $user = auth::user();
        if(!$user){
            return  response()->json([
                'status'=>'error',
                'message'=>'user not authenticated'
            ]);
        }

        if (!$user->is_admin) {
            return response()->json([
                'status'=>'error',
                'message'=>'Operation not allowed!'
            ]);

        }


        $payments = Payment::where($request->payment_id)
        ->update([
            'status'=>$request->approval_status,
            
        ]);



        return response()->json([
            'status'=>'error',
            'message'=>'Payment status updated successfully'
        ]);

    }


    public function updatePaymentStatusInformation(Request $request){
        $request->validate([
            'order_number'=>'required',
            'status'=>'required',

        ]);
        

        $user = auth::user();
        if(!$user){
            return  response()->json([
                'status'=>'error',
                'message'=>'user not authenticated'
            ]);
        }

        if (!$user->is_admin) {
            return response()->json([
                'status'=>'error',
                'message'=>'Operation not allowed!'
            ]);

        }


        $payments = Payment::where($request->payment_id)
        ->update([
            'status'=>$request->approval_status,
            
        ]);



        return response()->json([
            'status'=>'error',
            'message'=>'Payment status updated successfully'
        ]);

    }
}
