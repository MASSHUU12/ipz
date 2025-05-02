<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\SendMail;

class EmailUpdateController extends Controller
{
    /**
     * Zmienia email użytkownika, czyści email_verified_at i wysyła link weryfikacyjny.
     */
    public function update(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();  // dzięki PHPDoc wiemy, że to nie jest null

        // 1) Podmień email i wyczyść flagę weryfikacji:
        $user->forceFill([
            'email'             => $request->input('email'),
            'email_verified_at' => null,
        ])->save();

        // 2) Wyślij link weryfikacyjny na nowy email:
        SendMail::sendMail(
            $user->email,
            $user->id,
            'emailverify'
        );

        return response()->json([
            'message' => 'E-mail został zaktualizowany, wysłaliśmy link weryfikacyjny.',
        ], 200);
    }
}
