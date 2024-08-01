<?php

namespace LaravelBatchRequests;

use Illuminate\Support\Collection;
use LaravelBatchRequests\Exceptions\BatchRequestException;

class BatchRequest
{
    protected $requests;
    protected $responses;
    protected $config;
    protected $singleRequestHandler;

    public function __construct(SingleRequestHandler $singleRequestHandler, array $requests = [], array $config = [])
    {
        $this->requests = collect($requests);
        $this->responses = collect();
        $this->config = $config + [
                'max_requests_per_batch' => config('batch-requests.max_requests_per_batch', 10),
                'timeout' => config('batch-requests.timeout', 30),
            ];
        $this->singleRequestHandler = $singleRequestHandler;
    }

    public static function make(array $requests = [])
    {
        return new static(app(SingleRequestHandler::class), $requests, []);
    }

    public function process()
    {
        $this->validateBatchSize();

        $this->responses = $this->requests->map(function ($requestData, $key) {
            return $this->processSingleRequest($requestData, $key);
        });

        return $this;
    }

    protected function validateBatchSize()
    {
        if ($this->requests->count() > $this->config['max_requests_per_batch']) {
            throw new BatchRequestException("Batch request limit exceeded. Maximum allowed: {$this->config['max_requests_per_batch']}");
        }
    }

    protected function processSingleRequest($requestData, $key)
    {
        try {
            return $this->singleRequestHandler->handle($requestData, $key);
        } catch (\Exception $e) {
            return $this->handleException($requestData, $key, $e);
        }
    }

    protected function handleException($requestData, $key, \Exception $e)
    {
        return [
            'id' => $requestData['id'] ?? $key,
            'status' => 500,
            'error' => $e->getMessage()
        ];
    }

    public function getResponses(): Collection
    {
        return $this->responses;
    }
}