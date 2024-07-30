<?php

namespace LaravelBatchRequests\Exceptions;

use Exception;

class BatchRequestException extends Exception
{
    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
        ], 500);
    }
}


