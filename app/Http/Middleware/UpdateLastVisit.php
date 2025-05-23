<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastVisit {
    public function handle(Request $request, Closure $next): Response
    {
        // Try multiple authentication methods
        $user = null;

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            //echo ('User 1 from sanctum guard: ' . $user->id);
        }
        else if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            //echo ('User 2 from sanctum guard: ' . $user->id);
        }
        else if ($request->user()) {
            $user = $request->user();
            //echo ('User 3 from request: ' . $user->id);
        }

        if ($user) {
            $user->withoutTimestamps(function () use ($user) {
                $user->update(['last_visit' => now()]);
            });
        }
        

        //die;
        
        return $next($request);
    }
}