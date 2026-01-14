<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreCancellationPolicyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:cancellation_policies,name',
            'user_type' => 'required|in:rider,driver',
            'zone_id' => 'required|exists:zones,id',
            'time_limit_minutes' => 'required|integer|min:0',
            'penalty_amount' => 'nullable|numeric',
            'penalty_percent' => 'nullable|numeric',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('The Name field is required.'),
            'name.string' => __('The Name must be a string.'),
            'name.unique' => __('The Name field must be unique.'),
            'user_type.required' => __('The User Type field is required.'),
            'user_type.in' => __('The selected User Type is invalid.'),
            'zone_id.required' => __('The Zone field is required.'),
            'zone_id.exists' => __('The selected Zone is invalid.'),
            'time_limit_minutes.required' => __('The Time Limit Minutes field is required.'),
            'time_limit_minutes.integer' => __('The Time Limit Minutes must be an integer.'),
            'time_limit_minutes.min' => __('The Time Limit Minutes must be at least 0.'),
            'penalty_amount.numeric' => __('The Penalty Amount must be a number.'),
            'penalty_percent.numeric' => __('The Penalty Percent must be a number.'),
            'description.string' => __('The Description must be a string.'),
            'status.required' => __('The Status field is required.'),
            'status.in' => __('The selected Status is invalid.')
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
