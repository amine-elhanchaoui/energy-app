<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    // ...$roles means that we can pass multiple roles to this middleware, and it will check if the user has any of the specified roles.
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'you must be logged in to access this page'
            ], 401);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'you do not have the required role to access this page'
        ], 403);
    }
}