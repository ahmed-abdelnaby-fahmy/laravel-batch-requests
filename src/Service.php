<?php

namespace LaravelBatchRequests;


use LaravelBatchRequests\Exceptions\BatchRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class Service
{
    private $request;

    public function __construct(private $serviceName, private $body)
    {
    }

    protected function getService()
    {
        $route = Route::getRoutes()->getByName($this->serviceName);
        if (!$route) throw new BatchRequestException($this->serviceName . " not found");
        return $route;
    }

    public function run()
    {
        try {
            $this->configuration();
            $this->request->headers->add(request()->headers->all());
            $response = app()->handle($this->request)->getContent();
            app()->terminate();

            return json_decode($response);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function configuration()
    {
        $validator = Validator::make([
            'service' => $this->serviceName,
            'body' => $this->body
        ], [
            'service' => 'required|string|max:125',
            'body' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new BatchRequestException($validator->messages()->toArray(), 422);
        }
        $this->request = Request::create($this->getFullUri(), $this->getService()->methods()[0], $this->body);

    }

    public function getFullUri()
    {
        return 'https://' . \request()->getHost() . '/' . $this->getService()->uri();
    }

    public function getName()
    {
        return $this->serviceName;
    }
}
