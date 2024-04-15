<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;
use Illuminate\Support\Facades\Password;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\PasswordResetMail;

class AuthController extends Controller
{
    /**
     * Registration Req
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                400,
            );
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('ShoppingApp')->accessToken;

        return response()->json(
            [
                'status' => 'success',
                'message' => 'User successfully registered',
                'data' => [
                    'user_id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'token' => $token,
                ],
            ],
            200,
        );
    }

    /**
     * Login Req
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            // Revoke old tokens for the authenticated user
            Token::where('user_id', $credentials['email'])
                ->where('revoked', false)
                ->update(['revoked' => true]);

            $accessToken = Auth::user()->createToken('ShoppingApp')->accessToken;

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'token' => $accessToken,
                        'userId' => Auth::user()->id,
                        'isAdmin' => Auth::user()->is_admin,
                    ],
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Invalid username or password',
                ],
                401,
            );
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                400,
            );
        }

        $user = Auth::user();

        // Check if the current password matches the one in the database
        if (Hash::check($request->current_password, $user->password)) {
            // Update the password
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Password updated successfully',
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Current password is incorrect',
                ],
                401,
            );
        }
    }

    public function logout(Request $request)
    {
        // Revoke the access token for the authenticated user
        $request->user()->token()->revoke();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'User successfully logged out',
            ],
            200,
        );
    }

    public function userInfo()
    {
        $user = auth()->user();

        return response()->json(['user' => $user], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $errorMessage,
                ],
                400,
            );
        }

        $token = Str::random(60);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = env('APPLICATION_URL') . '?token=' . $token;

        Mail::to($request->email)->send(new PasswordResetMail($resetUrl));

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset email sent ',
        ]);
    }

    /**
     * Reset Password Req
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $passwordReset = DB::table('password_reset_tokens')

            ->where('token', $request->token)
            ->first();

            
            if (!$passwordReset) {
                return response()->json(['message' => 'Invalid token'], 400);
            }
            
            $email = $passwordReset->email;
        $user = User::where('email', $email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        return response()->json(['message' => 'Password reset successful']);
    }
}
