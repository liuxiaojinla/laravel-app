<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Xin\Hint\Facades\Hint;

class ResetPasswordLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('username')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw Error::validationException(__($status));
        }

        return Hint::success(__('passwords.sent'), null, ['status' => __($status)]);
    }
}
