<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (
            (!$user->email || $user->email_verified_at === null)
            && (!$user->phone_number || $user->phone_number_verified_at === null)
        ) {
            return response([
                'message' => 'You need to verify either email or phone number.'
            ], 403);
        }

        return $next($request);
    }
}
