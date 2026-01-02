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
            'min_minutes' => 'required|integer',
            'max_minutes' => 'required|integer',
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
            'min_minutes.required' => __('The Min Minutes field is required.'),
            'min_minutes.integer' => __('The Min Minutes must be an integer.'),
            'max_minutes.required' => __('The Max Minutes field is required.'),
            'max_minutes.integer' => __('The Max Minutes must be an integer.'),
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
