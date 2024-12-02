<?php

namespace App\Admin\Controllers\Auth;

use App\Admin\Requests\Auth\LoginRequest;
use App\Admin\Services\AdminService;
use App\Http\Controller;
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
    public function store(LoginRequest $request, AdminService $adminService)
    {
        $user = $request->authenticate();

        // 更新数据
        $user->forceFill([
            'login_time'  => $this->request->time(),
            'login_ip'    => $this->request->ip(),
            'login_count' => $user->login_count + 1,
        ])->save();
        $adminService->updateCache($user);

        $request->session()->regenerate();

        return Hint::success(
            __('auth.successful'),
            null,
            [
                'info'  => $user->makeHidden([
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
