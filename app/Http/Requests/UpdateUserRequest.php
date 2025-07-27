<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'nullable|email|unique:users,email,' . $this->user->id,
            'phone' => 'nullable|unique:users,phone,' . $this->user->id,
            // 'image' => 'nullable',
            'activity' => 'nullable|in:active,inactive',
            'wallet' => 'nullable|numeric|min:0',
            // 'role' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => __('Email already exists'),
            'phone.unique' => __('Phone number already exists'),
            'wallet.numeric' => __('Wallet must be a number'),
            'wallet.min' => __('Wallet must be at least 0'),
            'activity.in' => __('Activity must be either active or inactive'),
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
