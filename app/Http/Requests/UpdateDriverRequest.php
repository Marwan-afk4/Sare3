<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
class UpdateDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 'name' => 'nullable|string',
            // 'email' => 'nullable|email|unique:users,email,' . $this->driver->id,
            // 'phone' => 'nullable|unique:users,phone,' . $this->driver->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password' => 'nullable|string|min:8',
            // 'activity' => 'nullable',
            'status' => 'nullable|in:approved,rejected',
            'wallet' => 'nullable|numeric|min:0',
            'activity' => ['required', Rule::in('active', 'inactive')],
            'zone_id' => 'nullable|exists:zones,id',
            // 'role' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'email.email' => __('The Email must be a valid email address.'),
            'email.unique' => __('The Email has already been taken.'),
            'phone.unique' => __('The Phone has already been taken.'),
            'password.min' => __('The Password must be at least 8 characters.'),
            'password.string' => __('The Password must be a string.'),
            'status.in' => __('The Status must be one of the following: approved, rejected.'),
            'zone_id.exists' => __('The selected zone does not exist.'),
            'activity.in'=> __('The Activity must be one of the following: active, inactive.'),
            'wallet.numeric'=> __('The Wallet must be a number.'),
            'wallet.min'=> __('The Wallet must be at least 0.'),
            'activity.required'=> __('The Activity field is required.'),
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
