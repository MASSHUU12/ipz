<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Mail\SendMail;

class EmailVerificationNotificationController extends Controller
{

	public function store(Request $request): RedirectResponse
	{
		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->intended(route('dashboard', absolute: false));
		}

		$request->user()->sendEmailVerificationNotification();

		SendMail::sendMail($request->user()->email, 'emailverify');

		return back()->with('status', 'verification-link-sent');
	}
}
