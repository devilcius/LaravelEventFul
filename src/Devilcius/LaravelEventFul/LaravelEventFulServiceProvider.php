<?php

namespace Devilcius\LaravelEventFul;

use Illuminate\Support\ServiceProvider;

class LaravelEventFulServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            base_path('vendor/devilcius/laravel-eventful/src/Devilcius/config/config.php') => config_path('eventful.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
                base_path('vendor/devilcius/laravel-eventful/src/Devilcius/config/config.php'), 'eventful'
        );
    }

}
