<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminSettingsController extends Controller
{

   

    //fetch all admin settings 
    public function index()
    {
        $adminSettings = AdminSettings::first();
        return response()->json([
            'status' => 'success',
            'message' => 'Admin settings fetched successfully',
            'data' => $adminSettings,
        ], 200);
    }

    public function addOrUpdateOfficeAddress(Request $request)
    {
        $request->validate([
            'office_address' => 'required|string|max:255',
        ]);

        $adminSettings = AdminSettings::firstOrCreate([], ['office_address' => $request->office_address]);
        $adminSettings->office_address = $request->office_address;
        $adminSettings->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Office address added or updated successfully',
            'data' => $adminSettings,
        ], 200);
    }

    public function addOrUpdateShippingCost(Request $request)
    {
        $request->validate([
            'shipping_cost_per_meter' => 'required|numeric',
        ]);

        $adminSettings = AdminSettings::firstOrCreate([], ['shipping_cost_per_meter' => $request->shipping_cost_per_meter]);
        $adminSettings->shipping_cost_per_meter = $request->shipping_cost_per_meter;
        $adminSettings->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Shipping cost added or updated successfully',
            'data' => $adminSettings,
        ], 200);
    }

    public function updateAccountNumbers (Request $request){
        $validator = Validator::make($request->all(), [
            'account_number_1' => 'required|string|max:255',
            'account_number_2' => 'required|string|max:255',
            'bank_name_1' => 'required|string|max:255',
            'bank_name_2' => 'required|string|max:255',
            'account_name_1' => 'required|string|max:255',
            'account_name_2' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
            'data' => null,
            ], 400);
        }

        $adminSettings = AdminSettings::first();
        if ($adminSettings) {
            $adminSettings->account_number_1 = $request->account_number_1;
            $adminSettings->account_number_2 = $request->account_number_2;
            $adminSettings->bank_name_1 = $request->bank_name_1;
            $adminSettings->bank_name_2 = $request->bank_name_2;
            $adminSettings->account_name_1 = $request->account_name_1;
            $adminSettings->account_name_2 = $request->account_name_2;
            $adminSettings->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Account details added or updated successfully',
            'data' => $adminSettings,
        ], 200);

      
    }

    

}