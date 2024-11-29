<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected static $allowModuleList = ['api', 'admin', 'web'];

    /**
     * @var string
     */
    protected static $defaultModule = 'web';

    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            [$module, $modulePath] = $this->moduleParse($this->app['request']->path());
            $this->registerRequestModuleMacros($module, $modulePath);

            // 加载全局路由文件
            if ($module === 'api') {
                Route::middleware('api')
                    ->prefix('api')
                    ->name('api.')
                    ->group(base_path('routes/api.php'));
            } elseif ($module === 'admin') {
                Route::middleware('admin')
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));
            } elseif ($module === 'notify') {
                Route::middleware('notify')
                    ->prefix('notify')
                    ->name('notify.')
                    ->group(base_path('routes/notify.php'));
            } else {
                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/web.php'));
            }

        });
    }


    /**
     * 解析模块
     * @param string $requestPath
     * @return array
     */
    protected function moduleParse(string $requestPath): array
    {
        $module = self::$defaultModule;
        $modulePath = $requestPath;

        if ($index = strpos($requestPath, '/')) {
            $module = substr($requestPath, 0, $index);
            if (in_array($module, self::$allowModuleList)) {
                $modulePath = substr($requestPath, $index + 1);
            } else {
                $module = self::$defaultModule;
            }
        } else {
            if (in_array($requestPath, self::$allowModuleList)) {
                $module = $requestPath;
                $modulePath = '';
            }
        }

        return [$module, $modulePath];
    }

    /**
     * 注册请求器相关宏操作
     * @return void
     */
    protected function registerRequestModuleMacros($module, $modulePath)
    {
        Request::macro('setPathInfo', function ($pathInfo) {
            // /** @var Request $this */
            $this->pathInfo = $pathInfo;
        });

        Request::macro('module', function () use ($module) {
            return $module;
        });

        Request::macro('modulePath', function () use ($modulePath) {
            return $modulePath;
        });
    }
}
