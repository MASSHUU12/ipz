<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required_without:phone_number|email|unique:users,email',
            'phone_number' => 'required_without:email|unique:users,phone_number|phone:INTERNATIONAL',
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:255',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
        ]);


		    $user = User::create([
			    'email' => $validated['email'] ?? null,
			    'phone_number' => $validated['phone_number'] ?? null,
			    'password' => password_hash($validated['password'], PASSWORD_DEFAULT)
		    ]);
		    $user->assignRole('User');
		    if ($request->has('email')) {
			    EmailVerificationNotificationController::store($user);
		    }

		   /* return response([
			    'message' => 'There was an error during user registration. Please try again.'
		    ], 500);*/


        $token = $user->createToken('token')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required_without:phone_number|email',
            'phone_number' => 'required_without:email|phone:INTERNATIONAL',
            'password' => 'required',
        ]);

        if ($request->has('email')) {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = User::where('phone_number', $request->phone_number)->first();
        }

        if ($user && $user->blocked_until && $user->blocked_until > now()) {
            return response(
                ['message' => 'Your account is temporarily blocked. Please try again later.'],
                401
            );
        }

        if (!$user || !password_verify($request->password, $user->password)) {
            $failed_login_limit = 5;
            $user->failed_login_attempts++;

            if ($user->failed_login_attempts >= $failed_login_limit) {
                $user->blocked_until = now()->addHours(4);
            }

            $user->save();

            return response([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $user->failed_login_attempts = 0;
        $user->blocked_until = null;
        $user->save();

        $response = [
            'user' => $user,
            'token' => $user->createToken($request->email ?? $request->phone_number)->plainTextToken
        ];
        return response($response, 201);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response([
            'message' => 'Tokens Revoked'
        ], 200);
    }

    public function validateToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            return response()->json([
                'valid' => true,
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }
}
