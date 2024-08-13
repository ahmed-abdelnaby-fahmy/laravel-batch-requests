<?php

namespace LaravelBatchRequests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SingleRequestHandler
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function handle($requestData, $key)
    {
        $request = $this->createRequest($requestData);

        try {
            $route = $this->findAndBindRoute($request);
            $response = $this->runRouteWithMiddleware($route, $request);
            return $this->formatSuccessResponse($requestData, $key, $response);
        } catch (ModelNotFoundException $e) {
            return $this->handleModelNotFound($requestData, $key, $e, $route ?? null, $request);
        } catch (NotFoundHttpException $e) {
            return $this->formatErrorResponse($requestData, $key, 404, 'Route Not Found');
        } catch (\Exception $e) {
            return $this->formatErrorResponse($requestData, $key, 500, $e->getMessage(), 'An unexpected error occurred');
        }
    }

    protected function createRequest($requestData)
    {
        $method = strtoupper($requestData['method'] ?? 'GET');
        $uri = $requestData['uri'] ?? '/';
        $parameters = $requestData['parameters'] ?? [];
        $headers = $requestData['headers'] ?? [];

        $request = Request::create($uri, $method, $parameters, [], [], $this->transformHeaders($headers));
        app()->instance('request', $request);

        return $request;
    }

    protected function transformHeaders(array $headers)
    {
        return collect($headers)->mapWithKeys(function ($value, $key) {
            return ["HTTP_" . strtoupper(str_replace('-', '_', $key)) => $value];
        })->all();
    }

    protected function findAndBindRoute(Request $request)
    {
        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        return $route;
    }

    protected function runRouteWithMiddleware($route, $request)
    {
        $substituteBindings = new SubstituteBindings($this->router);
        return $substituteBindings->handle($request, function ($request) use ($route) {
            return $route->run();
        });
    }

    protected function formatSuccessResponse($requestData, $key, $response)
    {
        if (!$response instanceof \Illuminate\Http\Response && !$response instanceof \Illuminate\Http\JsonResponse) {
            $response = new \Illuminate\Http\Response($response);
        }

        $body = $response->getContent();
        $contentType = $response->headers->get('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            $body = json_decode($body, true);
        }

        return [
            'id' => $requestData['id'] ?? $key,
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'body' => $body
        ];
    }

    protected function handleModelNotFound($requestData, $key, $e, $route, $request)
    {
        if ($route && $missingCallback = $route->getMissing()) {
            $response = $missingCallback($request, $e);
            return $this->formatSuccessResponse($requestData, $key, $response);
        }

        return $this->formatErrorResponse($requestData, $key, 404, $e->getMessage(), 'Requested resource not found');
    }

    protected function formatErrorResponse($requestData, $key, $status, $error, $message = null)
    {
        $response = [
            'id' => $requestData['id'] ?? $key,
            'status' => $status,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => [
                'status' => $status === 404 ? 'Not Found' : 'Error',
                'error' => $error
            ]
        ];

        if ($message) {
            $response['body']['msg'] = $message;
        }

        return $response;
    }
}