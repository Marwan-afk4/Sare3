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
            'password' => 'nullable',
            'image' => 'nullable',
            'activity' => 'nullable',
            'wallet' => 'nullable',
            'role' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'email.email' => __('The Email must be a valid email address.'),
            'email.unique' => __('The Email has already been taken.'),
            'phone.unique' => __('The Phone has already been taken.'),
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
