<?php

namespace App\Providers;

use App\View\Composers\ProfileComposer;
use App\View\Creators\ProfileCreator;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * 注册任何应用程序服务。
     */
    public function register(): void
    {
        // ...
    }

    /**
     * 引导任何应用程序服务。
     */
    public function boot(): void
    {
        // 使用基于类的合成器。。。
        Facades\View::composer('profile', ProfileComposer::class);

        // 使用基于闭包的合成器。。。
        Facades\View::composer('welcome', function (View $view) {
            // ...
        });

        Facades\View::composer('dashboard', function (View $view) {
            // ...
        });

        // 视图构造器
        Facades\View::creator('profile', ProfileCreator::class);
    }
}
