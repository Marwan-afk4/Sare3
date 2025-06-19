<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware {
    public function handle(Request $request, Closure $next, $role = null): Response {
        $user = Auth::user(); // Use default guard (sanctum for API)

        if ($user && $user->role === $role) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
