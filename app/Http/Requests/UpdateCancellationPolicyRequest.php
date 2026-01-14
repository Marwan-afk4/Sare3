<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCancellationPolicyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|unique:cancellation_policies,name,' . $this->route('cancellation_policy')->id,
            'user_type' => 'nullable|in:rider,driver',
            'zone_id' => 'nullable|exists:zones,id',
            'time_limit_minutes' => 'nullable|integer|min:0',
            'penalty_amount' => 'nullable|numeric',
            'penalty_percent' => 'nullable|numeric',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ];
    }

    public function messages()
    {
        return [
            'name.string' => __('The Name must be a string.'),
            'user_type.in' => __('The selected User Type is invalid.'),
            'zone_id.exists' => __('The selected Zone is invalid.'),
            'time_limit_minutes.integer' => __('The Time Limit Minutes must be an integer.'),
            'time_limit_minutes.min' => __('The Time Limit Minutes must be at least 0.'),
            'penalty_amount.numeric' => __('The Penalty Amount must be a number.'),
            'penalty_percent.numeric' => __('The Penalty Percent must be a number.'),
            'description.string' => __('The Description must be a string.'),
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
