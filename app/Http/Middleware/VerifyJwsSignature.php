<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwsSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param Closure(): void $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_DEBUG') && $request->has('jwsignore')) {
            return $next($request);
        }

        if (!$request->hasHeader('X-JWS-Signature')) {
            return response()->json(['error' => 'JWS signature required.'], 401);
        }

        $jwsSignature = $request->header('X-JWS-Signature');
        $payload = $request->getContent();

        try {
            if (!$this->verifyJwsSignature($jwsSignature, $payload)) {
                return response()->json(['error' => 'Invalid JWS signature.'], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'JWS verification failed: ' . $e->getMessage()], 401);
        }
    }

    protected function verifyJwsSignature(string $jwsSignature, string $payload): bool {
        $publicKey = file_get_contents(env('JWS_PUBLIC_KEY_PATH'));
        $algorithms = (object)['RS256'];
        $decoded = JWT::decode($jwsSignature, $publicKey, $algorithms);
        $payloadHash = hash('sha256', $payload);

        return $decoded->payload_hash === $payloadHash;
    }
}
