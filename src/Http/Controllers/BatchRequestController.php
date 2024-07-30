<?php

namespace LaravelBatchRequests\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelBatchRequests\BatchRequest;
use LaravelBatchRequests\Http\Requests\BatchRequestFormRequest;

class BatchRequestController extends Controller
{
    /**
     * Handle the incoming batch request.
     *
     * @param  BatchRequestFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(BatchRequestFormRequest $request)
    {
        $batchRequests = $request->validated()['requests'];

        $batchProcessor = new BatchRequest($batchRequests);
        $responses = $batchProcessor->process()->getResponses();

        return response()->json([
            'results' => $responses
        ]);
    }
}