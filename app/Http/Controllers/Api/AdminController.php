<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

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
    public function setAsAdmin(User $user): JsonResponse
    {
        $user->update(['is_admin' => true]);

        return $this->successResponse('User set as admin successfully');
    }

    /**
     * Disable admin status for the user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function disableAdmin(User $user): JsonResponse
    {
        $user->update(['is_admin' => false]);

        return $this->successResponse('User is no longer an admin');
    }

    /**
     * Generate a success JSON response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse(string $message): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message
        ], 200);
    }
}
