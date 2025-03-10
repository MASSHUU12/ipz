<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Console\Application;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required_without:phone_number|email|unique:users,email',
            'phone_number' => 'required_without:email|string|unique:users,phone_number|max:20',
            'password' => 'required|string|confirmed|max:255|min:8'
        ]);

        // TODO: Add proper phone number validation.

        $user = User::create([
            'email' => $validated['email'] ?? null,
            'phone_number' => $validated["phone_number"] ?? null,
            'password' => password_hash($validated['password'], PASSWORD_DEFAULT)
        ]);
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
            'phone_number' => 'required_without:email|string|max:20',
            'password' => 'required',
        ]);

        if ($request->email) {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = User::where('phone_number', $request->phone_number)->first();
        }

        if (!$user || !password_verify($request->password, $user->password)) {
            return response([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

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
}
