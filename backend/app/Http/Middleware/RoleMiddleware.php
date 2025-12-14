<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        $user = app('user');

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if (!$user->role) {
            return response()->json(['error' => 'User has no role assigned'], 403);
        }

        if ($user->role->name !== $role) {
            return response()->json([
                'error' => 'Forbidden. Required role: ' . $role,
                'your_role' => $user->role->name
            ], 403);
        }

        return $next($request);
    }
}