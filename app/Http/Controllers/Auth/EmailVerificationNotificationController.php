<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Mail\SendMail;

class EmailVerificationNotificationController extends Controller
{

	public static function store(User $user): RedirectResponse
	{
		if ($user->hasVerifiedEmail()) {
			return redirect()->intended(route('dashboard', absolute: false));
		}

		SendMail::sendMail($user->email, $user->id, 'emailverify');

		return back()->with('status', 'verification-link-sent');
	}
	public static function sendNotification(User $user): void
	{
		if (!$user->hasVerifiedEmail()) {
			SendMail::sendMail($user->email, $user->id, 'emailverify');
		}
	}

}