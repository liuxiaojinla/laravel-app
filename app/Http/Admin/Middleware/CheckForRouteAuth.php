<?php

namespace App\Http\Admin\Middleware;

use App\Http\Admin\Models\Admin;
use App\Http\Admin\Models\AdminMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Xin\Laravel\Strengthen\Http\Middleware\InteractsExcept;

class CheckForRouteAuth
{
    use InteractsExcept;

    /**
     * @var array
     */
    protected $except = [
        'index/index',
        'login/login',
        'login/logout',
        'index/captcha',
        'upload/callback',
    ];

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->defineAbility();

//        $path = $request->path($request);
//        if (!$this->isExcept($request) && !Gate::check('route', $path)) {
//            return ErrorController::output403();
//        }

        return $next($request);
    }

    /**
     * 定义能力
     */
    protected function defineAbility()
    {
        Gate::define('route', static function ($user, $checkUrl) {
            /** @var Admin $user */
            if ($user->is_admin) {
                return true;
            }

            $menus = AdminMenu::all();
            foreach ($menus as $item) {
                $url = $item['url'];
                if ($url == $checkUrl) {
                    return true;
                }
            }

            return false;
        });
    }

}
