<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitUploads
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'upload:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['message' => 'Too many upload attempts.'], 429, [
                'Retry-After' => $seconds,
            ]);
        }

        RateLimiter::hit($key, 3600);

        return $next($request);
    }
}
