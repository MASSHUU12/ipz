<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
	/**
	 * Mark the authenticated user's email address as verified.
	 */
	public function __invoke(EmailVerificationRequest $request): RedirectResponse
	{
		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->route('verify.email.thank-you');
		}

		if ($request->user()->markEmailAsVerified()) {
			$user = $request->user();
			event(new Verified($user));
		}

		return redirect()->route('verify.email.thank-you');
	}
}
