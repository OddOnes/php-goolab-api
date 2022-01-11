<?php

namespace OddOnes\GooLabApi;

use Illuminate\Support\ServiceProvider;

class GooLabApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/config/goolabapi.php');

        $this->publishes([$source => config_path('goolabapi.php')]);

        $this->mergeConfigFrom($source, 'goolabapi');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GooLabApi::class, function () {
            return new GooLabApi(config('goolabapi.key'));
        });

        $this->app->alias(GooLabApi::class, 'goolabapi');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [GooLabApi::class];
    }
}
