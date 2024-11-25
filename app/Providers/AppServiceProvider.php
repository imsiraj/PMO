<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ModelObserver;
use App\Models\User;
use App\Models\GlobalStatus;
use App\Models\Phases;
use App\Models\Priority;
use App\Models\Projects;
use App\Models\Roles;
use App\Models\Sprints;
use App\Models\Teams;
use App\Services\LoggingService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LoggingService::class, function ($app) {
            return new LoggingService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
