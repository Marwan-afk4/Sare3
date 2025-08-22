<?php

namespace App\Http\Requests;

use App\Models\RideRequestTimeLimit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreRideRequestTimeLimitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'time_limit_seconds' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Check if any record already exists
                    if (RideRequestTimeLimit::exists()) {
                        $fail(__('Only one request time limit can exist.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'time_limit_seconds.required' => __('The Time Limit Seconds field is required.'),
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
