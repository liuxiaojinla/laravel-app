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
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        $this->defineRoute('web', 'routes/web.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        $this->defineRoute('api', 'routes/api.php');
    }

    /**
     * 构建 Router
     * @param string $module
     * @return \Illuminate\Routing\RouteRegistrar
     */
    protected function defineRoute($module, $routePath)
    {
        $pluginName = $this->getCurrentPluginName();
        $pluginSnakeName = Str::snake($pluginName);
        $prefix = $module === 'web' ? '' : Str::snake($module);

        return Route::middleware($module)
            ->prefix($prefix . ($prefix ? '/' : '') . $pluginSnakeName)
            ->name($prefix . ($prefix ? '.' : '') . $pluginSnakeName . '.')
            ->namespace($this->moduleNamespace)
            ->group(module_path($pluginName, $routePath));
    }
}
