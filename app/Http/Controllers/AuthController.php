<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validated = $request->validate([
            'email' => 'required_without:phone_number|string|unique:users,email',
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
}
