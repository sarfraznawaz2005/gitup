<?php

namespace Sarfraznawaz2005\GitUp;

use Illuminate\Support\ServiceProvider;
use Sarfraznawaz2005\GitUp\Git\Git;

class GitUpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // routes
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/Http/routes.php';
        }

        // views
        $this->loadViewsFrom(__DIR__ . '/Views', 'gitup');

        // publish our files over to main laravel app
        $this->publishes([
            __DIR__ . '/Config/gitup.php' => config_path('gitup.php'),
            __DIR__ . '/Migrations' => database_path('migrations')
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('GitUp', function () {
            return new Git();
        });
    }
}
