<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRideRequestTimeLimitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'time_limit_seconds' => 'nullable|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'time_limit_seconds.integer' => __('The Time Limit Seconds field must be an integer.'),
            'time_limit_seconds.min' => __('The Time Limit Seconds field must be at least 1 second.'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new ValidationException($validator, response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]));
        }

        throw new ValidationException($validator);
    }
}
