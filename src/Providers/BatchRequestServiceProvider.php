<?php

namespace LaravelBatchRequests\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelBatchRequests\SingleRequestHandler;
use LaravelBatchRequests\BatchRequest;

class BatchRequestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SingleRequestHandler::class, function ($app) {
            return new SingleRequestHandler($app['router']);
        });

        $this->app->bind('batch-request', function ($app) {
            return new BatchRequest($app[SingleRequestHandler::class],[], []);
        });

        $this->mergeConfigFrom(
            __DIR__.'/../Config/batch-requests.php', 'batch-requests'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../Config/batch-requests.php' => config_path('batch-requests.php'),
        ], 'config');
    }
}