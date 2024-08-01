<?php

namespace LaravelBatchRequests\Facades;

use Illuminate\Support\Facades\Facade;

class BatchRequest extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'batch-request';
    }
}
