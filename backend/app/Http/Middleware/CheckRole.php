<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {

        if (!auth()->check() || auth()->user()->role->value !== $role) {
            return response()->json([
                'message' => 'Forbidden: You do not have the ' . $role . ' role.'
            ], 403);
        }

        return $next($request);
    }
}
