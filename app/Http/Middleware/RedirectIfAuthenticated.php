<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use App\Supports\WebServer;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Xin\Hint\Facades\Hint;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if (WebServer::shouldReturnJson($request)) {
                    return Hint::error("Guest identity is required to access.");
                } else {
                    return $this->redirectTo($request);
                }
            }
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    protected function redirectTo(Request $request)
    {
        $module = $request->module();

        $to = RouteServiceProvider::HOME;
        if ($module !== RouteServiceProvider::getDefaultModule()) {
            $to = "{$module}/";
        }

        return redirect($to);
    }
}
