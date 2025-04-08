<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Mail\SendMail;

class EmailVerificationNotificationController extends Controller
{

	public static function store(User $user)
	{
		if ($user->hasVerifiedEmail()) {
			return request()->wantsJson()
				? response()->json(['message' => 'Email already verified'])
				: redirect()->intended(route('dashboard', absolute: false));
		}

		SendMail::sendMail($user->email, $user->id, 'emailverify');

		return request()->wantsJson()
			? response()->json(['status' => 'verification-link-sent'])
			: back()->with('status', 'verification-link-sent');
	}

}