<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class UsersController extends Controller
{
    /**
     * Get details of the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetAllUsers()
    {
        $user = auth()->user();
        return response()->json([
            'status'=>'success',
            'data' => ['users'=>$user],
            
        ]);
    }

    /**
     * Show the profile of a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProfile($userId)
    {
        
        try {
            // Retrieve the authenticated user
            $user = auth()->user();

            // Check if the authenticated user matches the requested user ID
            if ($user->id == $userId) {
                // Return the user's profile details
                return response()->json([
                    'status' => 'success',
                    'data' => $user->toArray(),
                ], 200);
            } else {
                // Return an error if the authenticated user doesn't match the requested user ID
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You can only view your own profile.',
                ], 403); // 403 Forbidden status code
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e],200);
        }
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        // Retrieve the authenticated user
        $user = auth()->user();

        // Check if the authenticated user is updating their own profile
        if ($user->id == $request->user_id) {
            // Validate the request data as needed
            $request->validate([
                'firstname' => 'string|max:255',
                'lastname' => 'string|max:255',
                'address' => 'nullable|string',
                'country' => 'nullable|string',
                'city' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'profile_img' => 'nullable|string',
                // Add more fields as needed
            ]);

            // Update only the fields that are provided in the request
            $user->update($request->only([
                'firstname', 'lastname', 'address', 'country', 'city', 'phone_number', 'profile_img'
                // Add more fields as needed
            ]));

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()->toArray(),
            ], 200);
        } else {
            // Return an error if the authenticated user is not updating their own profile
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You can only update your own profile.',
            ], 403); // 403 Forbidden status code
        }
    }

    public function getUserAccessTokens($userId)
{
    $tokens = DB::table('oauth_access_tokens')
        ->where('user_id', $userId)
        ->get();

    return $tokens;
}
}

