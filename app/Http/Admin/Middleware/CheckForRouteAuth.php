<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\middleware;

use app\admin\controller\ErrorController;
use app\admin\model\AdminMenu;
use app\Request;
use Xin\ThinkPHP\Facade\Gate;
use Xin\ThinkPHP\Foundation\Middleware\InteractsExcept;

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
     * @param \app\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->defineAbility();

        $path = $request->path($request);
        if (!$this->isExcept($request) && !Gate::check('route', $path)) {
            return ErrorController::output403();
        }

        return $next($request);
    }

    /**
     * 定义能力
     */
    protected function defineAbility()
    {
        Gate::define('route', static function ($user, $checkUrl) {
            /** @var \app\admin\model\Admin $user */
            if ($user->is_admin) {
                return true;
            }

            $menus = AdminMenu::select();
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
