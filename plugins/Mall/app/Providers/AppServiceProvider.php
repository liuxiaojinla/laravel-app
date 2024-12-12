<?php

namespace Plugins\Mall\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Plugins\Mall\App\Listeners\ApiIndexListener;
use Plugins\Mall\App\Listeners\QuickSearchListener;
use Plugins\Mall\App\Listeners\URLChoiceInitListener;
use Plugins\Mall\App\Models\Goods;
use Xin\LaravelFortify\Plugin\AppServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register command Schedules.
     * @param Schedule $schedule
     */
    protected function registerCommandSchedules(Schedule $schedule)
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * @return void
     */
    protected function registerEnforceMorphMaps()
    {
        Relation::enforceMorphMap([
            'goods' => Goods::class,
        ]);
    }

    /**
     * @inerhitDoc
     */
    protected function registerEvents()
    {
        Event::listen('ApiIndex', ApiIndexListener::class);
        Event::listen('QuickSearch', QuickSearchListener::class);
        Event::listen('URLChoiceInit', URLChoiceInitListener::class);
    }
}
