<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TokenDebugMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            $user = $request->user();
            if ($user) {
                Log::info('Token used successfully', [
                    'user_id' => $user->id,
                    'token_id' => $user->currentAccessToken()?->id,
                    'endpoint' => $request->path(),
                    'method' => $request->method()
                ]);
            } else {
                Log::warning('Token provided but user not found', [
                    'token_hash' => hash('sha256', $request->bearerToken()),
                    'endpoint' => $request->path(),
                    'method' => $request->method()
                ]);
            }
        }
        
        return $next($request);
    }
}