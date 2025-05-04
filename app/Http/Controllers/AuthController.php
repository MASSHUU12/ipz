<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): Response
    {
        $data = $request->only(['email', 'phone_number', 'password']);

        // try {
        $user = User::create([
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        $user->assignRole('User');

        if ($user->email && !app()->runningUnitTests()) {
            EmailVerificationNotificationController::store($user);
        }
        // } catch (\Exception) {
        //     return response([
        //         'message' => 'There was an error during user registration. Please try again.'
        //     ], 500);
        // }

        return response([
            'user'  => $user,
            'token' => $user->createToken('token')->plainTextToken,
        ], 201);
    }

    public function login(LoginRequest $request): Response
    {
        $credentials = $request->only(['email', 'phone_number', 'password']);

        $user = $credentials['email']
            ? User::where('email', $credentials['email'])->first()
            : User::where('phone_number', $credentials['phone_number'])->first();

        if ($user && $user->blocked_until && $user->blocked_until->isFuture()) {
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

        $user->update([
            'failed_login_attempts' => 0,
            'blocked_until'         => null,
        ]);

        $response = [
            'user' => $user,
            'token' => $user->createToken($request->email ?? $request->phone_number)->plainTextToken
        ];
        return response($response, 201);
    }

    public function logout(Request $request): Response
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

    /**
     * Update the authenticated user’s password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Hash & save the new password
        $user->password = password_hash($request->input('new_password'), PASSWORD_DEFAULT);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }
}
