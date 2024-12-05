<?php

namespace Plugins\Coupon\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Xin\LaravelFortify\Plugin\AppServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([]);
    }

    /**
     * Register command Schedules.
     * @param Schedule $schedule
     */
    protected function registerCommandSchedules(Schedule $schedule)
    {
        $schedule->command('coupon:status-update')->everyMinute()->withoutOverlapping()->onOneServer();
    }
}
