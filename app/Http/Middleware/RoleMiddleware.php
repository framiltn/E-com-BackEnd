<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            \Log::warning('RoleMiddleware: User not authenticated');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();
        \Log::info('RoleMiddleware: Checking roles for user', [
            'user' => $user->email,
            'user_roles' => $user->getRoleNames()->toArray(),
            'required_roles' => $roles
        ]);

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        \Log::warning('RoleMiddleware: Unauthorized access', [
            'user' => $user->email,
            'user_roles' => $user->getRoleNames()->toArray(),
            'required_roles' => $roles
        ]);

        return response()->json(['message' => 'Unauthorized. Required role: ' . implode(' or ', $roles)], 403);
    }
}
