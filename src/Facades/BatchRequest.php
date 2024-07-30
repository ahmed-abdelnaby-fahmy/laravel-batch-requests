<?php

namespace LaravelBatchRequests\Facades;

use Illuminate\Support\Facades\Facade;

class BatchRequest extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'batch-request';
    }
}