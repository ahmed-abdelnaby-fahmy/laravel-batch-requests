<?php

namespace LaravelBatchRequests\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchRequestFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requests' => 'required|array',
            'requests.*.method' => 'required|string|in:GET,POST,PUT,PATCH,DELETE',
            'requests.*.id' => 'required|string',
            'requests.*.uri' => 'required|string',
            'requests.*.routeParameters' => 'sometimes|array',
            'requests.*.parameters' => 'sometimes|array',
            'requests.*.headers' => 'sometimes|array',
        ];
    }
}