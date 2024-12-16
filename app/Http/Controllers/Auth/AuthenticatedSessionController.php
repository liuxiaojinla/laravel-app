<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Supports\WebServer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Xin\Hint\Facades\Hint;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     * @throws ValidationException
     */
    public function store(LoginRequest $request): Response
    {
        $user = $request->authenticate();

        $request->session()->regenerate();

        return Hint::success(
            __('auth.successful'),
            null,
            [
                'info' => $user->makeHidden([
                    'password',
                ])->toArray(),
                'token' => WebServer::getEncryptSessionCookieValue(),
            ]
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Hint::success(__('auth.logged'),);
    }
}
