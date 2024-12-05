<?php

namespace Plugins\Mall\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
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

}
