<?php

namespace App\Providers;

use App\Admin\ModuleBootstrapServiceProvider as AdminModuleBootstrapServiceProvider;
use App\Http\ModuleBootstrapServiceProvider as ApiModuleBootstrapServiceProvider;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
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
     * @var string[]
     */
    protected static $allowModuleList = ['api', 'admin', 'web'];
    /**
     * @var string
     */
    protected static $defaultModule = 'web';

    /**
     * @return string
     */
    public static function getDefaultModule(): string
    {
        return self::$defaultModule;
    }

    /**
     * @param string $defaultModule
     * @return void
     */
    public static function setDefaultModule(string $defaultModule): void
    {
        self::$defaultModule = $defaultModule;
    }

    /**
     * @return string[]
     */
    public static function getAllowModuleList(): array
    {
        return self::$allowModuleList;
    }

    /**
     * @param array $allowModuleList
     * @return void
     */
    public static function setAllowModuleList(array $allowModuleList): void
    {
        self::$allowModuleList = $allowModuleList;
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        [$module, $modulePath] = $this->moduleParse($this->app['request']->path());
        $this->registerRequestModuleMacros($module, $modulePath);

        $this->routes(function () use ($module, $modulePath) {
            // 加载全局路由文件
            if ($module === 'api' || app()->runningInConsole()) {
                Route::middleware('api')
                    ->prefix('api')
                    ->name('api.')
                    ->group(base_path('routes/api/index.php'));
                $this->app->register(ApiModuleBootstrapServiceProvider::class);
            }

            if ($module === 'admin' || app()->runningInConsole()) {
                Route::middleware('admin')
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin/index.php'));
                $this->app->register(AdminModuleBootstrapServiceProvider::class);
            }

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web/index.php'));
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
