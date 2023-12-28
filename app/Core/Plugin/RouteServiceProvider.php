<?php

namespace App\Core\Plugin;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    use CurrentPlugin;

    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = '';

    public function register()
    {
        parent::register();

        $pluginName = $this->getCurrentPluginName();
        $this->moduleNamespace = "Plugins\\{$pluginName}\\app\\Http\\Controllers";
    }

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * 构建 Router
     * @param string $prefix
     * @return \Illuminate\Routing\RouteRegistrar
     */
    protected function newRouter($routePath, $prefix = '')
    {
        $pluginName = $this->getCurrentPluginName();
        $pluginSnakeName = Str::snake($pluginName);

        return Route::prefix($prefix . ($prefix ? '/' : '') . $pluginSnakeName)
            ->name($prefix . ($prefix ? '.' : '') . $pluginSnakeName . '.')
            ->namespace($this->moduleNamespace)
            ->group(module_path($pluginName, $routePath));
    }
}
