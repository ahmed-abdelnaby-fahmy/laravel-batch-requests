<?php

namespace LaravelBatchRequests\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelBatchRequests\BatchRequest;
use LaravelBatchRequests\SingleRequestHandler;
use LaravelBatchRequests\Http\Requests\BatchRequestFormRequest;

class BatchRequestController extends Controller
{
    protected $router;
    protected $singleRequestHandler;

    public function __construct(SingleRequestHandler $singleRequestHandler)
    {
        $this->singleRequestHandler = $singleRequestHandler;
    }

    /**
     * Handle the incoming batch request.
     *
     * @param  BatchRequestFormRequest  $request
     * @param  Purchase  $purchase
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(BatchRequestFormRequest $request)
    {
        $batchRequests = $request->validated()['requests'];

        $config = [
            'max_requests_per_batch' => config('batch-requests.max_requests_per_batch', 10),
            'timeout' => config('batch-requests.timeout', 30),
        ];

        $batchProcessor = new BatchRequest($this->singleRequestHandler,$batchRequests, $config);
        $responses = $batchProcessor->process()->getResponses();

        return response()->json([
            'results' => $responses
        ]);
    }
}