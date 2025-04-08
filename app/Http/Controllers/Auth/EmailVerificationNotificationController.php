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
		if ($user()->hasVerifiedEmail()) {
			return redirect()->intended(route('dashboard', absolute: false));
		}

		$user()->sendEmailVerificationNotification();

		SendMail::sendMail($user()->email, 'emailverify');

		return back()->with('status', 'verification-link-sent');
	}
}