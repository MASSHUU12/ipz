<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            return response()->json([
                'error' => 'JWS verification failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function verifyJwsSignature(string $jwsSignature, string $payload): bool {
        $publicKey = file_get_contents(env('JWS_PUBLIC_KEY_PATH'));
        if (!$publicKey) {
            return response()->json(['error' => 'Public key not found.'], 500);
        }

        list($encodedHeader, $encodedPayload, $encodedSignature) = explode('.', $jwsSignature);
        $dataToVerify = $encodedHeader . '.' . $encodedPayload;
        $signature = base64UrlDecode($encodedSignature);

        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if (!$publicKeyResource) {
            return response()->json(['error' => 'Invalid public key.'], 500);
        }

        $verified = openssl_verify($dataToVerify, $signature, $publicKeyResource, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            return response()->json(['error' => 'Signature verification failed.'], 401);
        }

        return $verified === 1;
    }

    function base64UrlDecode(string $input): string {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $input));
    }
}
