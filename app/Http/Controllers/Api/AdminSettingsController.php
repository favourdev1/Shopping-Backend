<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use Illuminate\Support\Facades\Auth;

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

    

}