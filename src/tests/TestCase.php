<?php

namespace LaravelBatchRequests\Tests;

use LaravelBatchRequests\BatchRequestServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [BatchRequestServiceProvider::class];
    }

    protected function defineRoutes($router)
    {
        $router->get('/api/users/{id}', function ($id) {
            return response()->json(['id' => $id, 'name' => 'Test User']);
        });

        $router->post('/api/posts', function () {
            return response()->json(['id' => 1, 'title' => request('title')], 201);
        });
    }
}