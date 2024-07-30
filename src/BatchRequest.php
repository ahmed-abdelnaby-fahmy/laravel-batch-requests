<?php

namespace LaravelBatchRequests;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelBatchRequests\Exceptions\BatchRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BatchRequest
{
    protected $requests;
    protected $responses;
    protected $config;

    public function __construct(array $requests = [], array $config = [])
    {
        $this->requests = collect($requests);
        $this->responses = collect();
        $this->config = $config + [
                'max_requests_per_batch' => config('batch-requests.max_requests_per_batch', 10),
                'timeout' => config('batch-requests.timeout', 30),
            ];
    }

    public function process()
    {
        if ($this->requests->count() > $this->config['max_requests_per_batch']) {
            throw new BatchRequestException("Batch request limit exceeded. Maximum allowed: {$this->config['max_requests_per_batch']}");
        }

        $this->responses = $this->requests->map(function ($requestData, $key) {
            try {
                return $this->handleSingleRequest($requestData, $key);
            } catch (\Exception $e) {
                return [
                    'id' => $requestData['id'] ?? $key,
                    'status' => 500,
                    'error' => $e->getMessage()
                ];
            }
        });

        return $this;
    }

    protected function handleSingleRequest($requestData, $key)
    {
        $method = strtoupper($requestData['method'] ?? 'GET');
        $uri = $requestData['uri'] ?? '/';
        $parameters = $requestData['parameters'] ?? [];
        $headers = $requestData['headers'] ?? [];

        $request = Request::create($uri, $method, $parameters, [], [], $this->transformHeaders($headers));
        app()->instance('request', $request);
        try {
            $route = Route::getRoutes()->match($request);
            $response = $route->run();
            $body = json_decode($response->getContent(), true);
        } catch (NotFoundHttpException $e) {
            return [
                'id' => $requestData['id'] ?? $key,
                'status' => 404,
                'error' => 'Not Found'
            ];
        }

        return [
            'id' => $requestData['id'] ?? $key,
            'status' => $response->getStatusCode(),
            'body' => $body
        ];
    }

    protected function transformHeaders(array $headers)
    {
        return collect($headers)->mapWithKeys(function ($value, $key) {
            return ["HTTP_" . strtoupper(str_replace('-', '_', $key)) => $value];
        })->all();
    }

    public function getResponses(): Collection
    {
        return $this->responses;
    }
}