<?php

namespace App\Providers;

use App\Services\Module\ModuleManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
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
//            // 加载全局路由文件
//            Route::middleware('web')
//                ->namespace($this->namespace)
//                ->group(base_path('routes/web.php'));
            // 加载模块路由文件
            /** @var ModuleManager $moduleManager */
            $moduleManager = $this->app['module'];
            $moduleManager->run($this->app['request']);
        });
    }
}
