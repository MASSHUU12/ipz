<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOptional
{
    /**
     * Handle an incoming request.
     *
     * This middleware attempts to authenticate the user via the "sanctum" guard.
     * If authentication is successful, the user context is set for the request.
     * If not, the request proceeds as a guest without throwing an authentication exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            Auth::shouldUse('sanctum');
            Auth::authenticate();
        }

        return $next($request);
    }
}
