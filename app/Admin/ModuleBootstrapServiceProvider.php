<?php

namespace App\Admin;

use App\Admin\Services\AdminService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Xin\Menu\Contracts\Factory;

class ModuleBootstrapServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        config([
            'auth.guards.admin' => array_merge([
                'driver' => 'session',
                'provider' => 'admins',
            ], config('auth.guards.admin', [])),

            'auth.providers.admins' => array_merge([
                'driver' => 'admin',
                'model' => \App\Admin\Models\Admin::class,
            ], config('auth.providers.admins', [])),

            'auth.passwords.admins' => array_merge([
                'provider' => 'admins',
                'table' => 'password_reset_tokens',
                'expire' => 60,
                'throttle' => 60,
            ], config('auth.passwords.admins', [])),
        ]);

        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * @return void
     */
    public function boot()
    {
        Auth::shouldUse('admin');
        Auth::provider('admin', function (Application $app, array $config) {
            return $app->make(AdminService::class, [
                'config' => $config,
            ]);
        });

        Auth::resolved(function ($auth) {
            //            dd(config('auth'));
        });

        $this->booted(function (Factory $factory){
            $factory->menu()->refresh();
        });
    }
}
