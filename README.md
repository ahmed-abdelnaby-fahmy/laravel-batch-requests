# Laravel Batch Requests

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aef/laravel-batch-requests.svg?style=flat-square)](https://packagist.org/packages/aef/laravel-batch-requests)
[![Total Downloads](https://img.shields.io/packagist/dt/aef/laravel-batch-requests.svg?style=flat-square)](https://packagist.org/packages/aef/laravel-batch-requests)
[![License](https://img.shields.io/packagist/l/aef/laravel-batch-requests.svg?style=flat-square)](https://packagist.org/packages/aef/laravel-batch-requests)

A Laravel package for efficiently handling multiple API requests in a single batch operation, reducing network overhead
and improving performance for bulk operations.

## Installation

You can install the package via composer:

```bash
composer require aef/laravel-batch-requests
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="LaravelBatchRequests\BatchRequestServiceProvider" --tag="config"
```

This will create a config/batch-requests.php file where you can modify the package settings.

# Usage

```php
use LaravelBatchRequests\Http\Controllers\BatchRequestController;

Route::post('batch', [BatchRequestController::class, 'process']);

```

To use the batch request functionality, send a POST request to the `/api/batch` endpoint with a JSON payload containing
an array of requests:

```json
{
  "requests": [
    {
      "id": "get-user",
      "method": "GET",
      "uri": "/api/users/1",
      "headers": {
        "Accept": "application/json"
      }
    },
    {
      "id": "create-post",
      "method": "POST",
      "uri": "/api/posts",
      "parameters": {
        "title": "New Post",
        "content": "This is the content of the new post."
      },
      "headers": {
        "Content-Type": "application/json",
        "Accept": "application/json"
      }
    }
  ]
}
```

The response will contain the results of all the batched requests, with the body returned as parsed JSON:

```json
{
  "results": [
    {
      "id": "get-user",
      "status": 200,
      "headers": {
        "Content-Type": "application/json"
      },
      "body": {
        "id": 1,
        "name": "John Doe"
      }
    },
    {
      "id": "create-post",
      "status": 201,
      "headers": {
        "Content-Type": "application/json"
      },
      "body": {
        "id": 101,
        "title": "New Post",
        "content": "This is the content of the new post."
      }
    }
  ]
}
```