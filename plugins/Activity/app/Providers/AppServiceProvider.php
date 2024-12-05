<?php

namespace Plugins\Activity\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Plugins\Activity\app\Console\StatusUpdateCommand;
use Xin\LaravelFortify\Plugin\AppServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands()
    {
        $this->commands([
            StatusUpdateCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     * @param Schedule $schedule
     */
    protected function registerCommandSchedules(Schedule $schedule)
    {
        $schedule->command('activity:status-update')->everyMinute()->withoutOverlapping()->onOneServer();
    }
}
