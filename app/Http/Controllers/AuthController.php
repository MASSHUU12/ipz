<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email',
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
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    Auth::login($user);

    logger('✅ Użytkownik zarejestrowany: ' . $user->email);

    return Inertia::location('/dashboard');
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
        return back()->withErrors([
            'email' => 'Your account is temporarily blocked. Please try again later.',
        ]);
    }

    if (!$user || !password_verify($request->password, $user->password)) {
        $failed_login_limit = 5;
        if ($user) {
            $user->failed_login_attempts++;
            if ($user->failed_login_attempts >= $failed_login_limit) {
                $user->blocked_until = now()->addHours(4);
            }
            $user->save();
        }

        return back()->withErrors([
            'email' => 'The provided credentials are incorrect.',
        ]);
    }

    $user->failed_login_attempts = 0;
    $user->blocked_until = null;
    $user->save();

    Auth::login($user);
    $request->session()->regenerate(); 

    return Inertia::location('/dashboard'); 
}


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response([
            'message' => 'Tokens Revoked'
        ], 200);
    }
}
