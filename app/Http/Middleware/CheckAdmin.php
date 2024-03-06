<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        
        // Check if the user is an admin
        if ($request->user() && $request->user()->is_admin) {
            return $next($request);
        }

        // If not an admin, return unauthorized response
        return response()->json([
            'status'=>'error',
            'message' => 'Unauthorized Action. Admin access required.'], 403);
    }
}
