<?php

namespace LaravelBatchRequests\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelBatchRequests\Facades\BatchRequest;

class BatchRequestServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('batch-request', function ($app) {
            return new BatchRequest([]);
        });

        $this->mergeConfigFrom(
            __DIR__.'/../Config/batch-requests.php', 'batch-requests'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../Config/batch-requests.php' => config_path('batch-requests.php'),
        ], 'config');
    }
}