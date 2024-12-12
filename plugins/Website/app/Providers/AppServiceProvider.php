<?php

namespace Plugins\Website\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Plugins\Website\App\Models\Article;
use Plugins\Website\App\Models\Cases;
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
     * @inerhitDoc
     */
    protected function registerEnforceMorphMaps()
    {
        Relation::enforceMorphMap([
            Article::MORPH_TYPE => Article::class,
            Cases::MORPH_TYPE   => Cases::class,
        ]);
    }
}
