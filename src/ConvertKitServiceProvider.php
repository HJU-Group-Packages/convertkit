<?php

namespace HJUGroup\ConvertKit;

use Illuminate\Support\ServiceProvider;

class ConvertKitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('convertkit.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php',
            'convertkit'
        );

        // Register the main class to use with the facade
        $this->app->singleton('convertkit', function () {
            return new ConvertKit;
        });
    }
}
