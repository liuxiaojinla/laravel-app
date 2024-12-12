<?php

namespace Plugins\Shop\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Plugins\Shop\App\Listeners\AdminQuickSearchListener;
use Plugins\Shop\App\Models\Shop;
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
            'shop' => Shop::class,
        ]);
    }


    /**
     * @inerhitDoc
     */
    protected function registerEvents()
    {
        Event::listen('AdminQuickSearch', AdminQuickSearchListener::class);
    }

}
