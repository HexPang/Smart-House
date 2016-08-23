<?php

namespace App\Providers;

use App\Services\RemoteService;
use Illuminate\Support\ServiceProvider;

class RemoteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RemoteService::class, function ($app) {
            return new RemoteService($app);
        });
    }
}
