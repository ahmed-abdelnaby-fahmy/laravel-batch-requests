<?php

namespace LaravelBatchRequests\Tests;

use LaravelBatchRequests\BatchRequest;

class BatchRequestTest extends TestCase
{
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

        $batchRequest = new BatchRequest($batchData);
        $responses = $batchRequest->process()->getResponses();

        $this->assertCount(2, $responses);

        $getUserResponse = $responses[0];
        $this->assertEquals('get-user', $getUserResponse['id']);
        $this->assertEquals(200, $getUserResponse['status']);
        $this->assertJsonStringEqualsJsonString(
            '{"id": 1, "name": "Test User"}',
            $getUserResponse['body']
        );

        $createPostResponse = $responses[1];
        $this->assertEquals('create-post', $createPostResponse['id']);
        $this->assertEquals(201, $createPostResponse['status']);
        $this->assertJsonStringEqualsJsonString(
            '{"id": 1, "title": "Test Post"}',
            $createPostResponse['body']
        );
    }
}