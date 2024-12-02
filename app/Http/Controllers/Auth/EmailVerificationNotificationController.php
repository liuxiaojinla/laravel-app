<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return Hint::result([
                'status' => 'verified-email',
            ]);
            //            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $request->user()->sendEmailVerificationNotification();

        return Hint::result([
            'status' => 'verification-link-sent',
        ]);
    }
}
