<?php

namespace LaravelBatchRequests\Tests;

use LaravelBatchRequests\Facades\BatchRequest;
use Orchestra\Testbench\TestCase as Orchestra;

class BatchRequestTest extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [\LaravelBatchRequests\Providers\BatchRequestServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'BatchRequest' => BatchRequest::class,
        ];
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

    protected function setUp(): void
    {
        parent::setUp();

        // Define the routes to be used in the test
        $this->defineRoutes($this->app['router']);
    }

    public function testBatchRequestProcessing()
    {
        $batchData = [
            [
                'id' => 'get-user',
                'method' => 'GET',
                'uri' => '/api/users/1',
            ],
            [
                'id' => 'create-post',
                'method' => 'POST',
                'uri' => '/api/posts',
                'parameters' => ['title' => 'Test Post'],
            ],
        ];

        $responses = BatchRequest::make($batchData)->process()->getResponses();
        $this->assertCount(2, $responses);

        $getUserResponse = $responses[0];
        $this->assertEquals('get-user', $getUserResponse['id']);
        $this->assertEquals(200, $getUserResponse['status']);
        $this->assertJsonStringEqualsJsonString(
            json_encode(["id" => "1", 'name' => "Test User"]),
            json_encode($getUserResponse['body'])
        );


        $createPostResponse = $responses[1];
        $this->assertEquals('create-post', $createPostResponse['id']);
        $this->assertEquals(201, $createPostResponse['status']);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['id' => 1, 'title' => 'Test Post'], JSON_UNESCAPED_UNICODE),
            json_encode($createPostResponse['body'])
        );
    }

    public function testBatchRequestHandlesErrors()
    {
        $batchData = [
            [
                'id' => 'non-existent-route',
                'method' => 'GET',
                'uri' => '/api/non-existent',
            ],
        ];

        $responses = BatchRequest::make($batchData)->process()->getResponses();
        $non_existent = $responses[0];
        $this->assertEquals($non_existent['id'], 'non-existent-route');
        $this->assertEquals($non_existent['body']['status'], "Not Found");
    }
}