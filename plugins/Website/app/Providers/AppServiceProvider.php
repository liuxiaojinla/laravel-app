<?php

namespace Plugins\Website\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Plugins\Website\App\Models\WebsiteArticle;
use Plugins\Website\App\Models\WebsiteCase;
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
            WebsiteArticle::MORPH_TYPE => WebsiteArticle::class,
            WebsiteCase::MORPH_TYPE => WebsiteCase::class,
        ]);
    }
}
