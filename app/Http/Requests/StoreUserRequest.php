<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    protected string $defaultRole = 'user';
    public function authorize()
    {
        return true;
    }
    public function prepareForValidation()
    {
        $this->merge([
            'role' => $this->defaultRole,
            'activity' => 'active',
            'wallet' => 0,
        ]);
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required',
            'image' => 'nullable',
            'activity' => 'required|in:active,inactive',
            'wallet' => 'nullable|numeric',
            'role' => 'required|in:admin,driver,user',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('The Name field is required.'),
            'name.string' => __('The Name must be a string.'),
            'email.email' => __('The Email must be a valid email address.'),
            'email.unique' => __('The Email has already been taken.'),
            'phone.unique' => __('The Phone has already been taken.'),
            'phone.required' => __('The Phone field is required.'),
            'password.required' => __('The Password field is required.'),
            'role.required' => __('The Role field is required.')
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

    public function setRole(string $role): void
    {
        $this->defaultRole = $role;
    }
}
