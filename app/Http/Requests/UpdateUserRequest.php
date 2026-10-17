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
        if ($this->filled('_password_action')) {
            if ($this->input('_password_action') === 'remove') {
                return [
                    '_password_action' => 'required|in:remove',
                ];
            }

            return [
                '_password_action' => 'required|in:set',
                'user_password' => 'required|string|min:8|confirmed',
            ];
        }

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
            'user_password.required' => __('The Password field is required.'),
            'user_password.min' => __('The Password must be at least 8 characters.'),
            'user_password.confirmed' => __('The Password confirmation does not match.'),
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
