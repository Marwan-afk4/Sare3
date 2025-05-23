<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware{
    public function handle(Request $request, Closure $next, $role = null): Response {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === $role) {
            return $next($request);
        }
        abort(403, 'Unauthorized action.');
    }
}
