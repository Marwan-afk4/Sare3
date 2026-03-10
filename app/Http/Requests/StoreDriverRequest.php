<?php

namespace App\Http\Requests;

use App\Models\DocumentType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    protected string $defaultRole = 'driver';

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
        $rules = [
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|string|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'activity' => ['nullable', Rule::in('active', 'inactive')],
            'wallet' => 'nullable|numeric|min:0',
            'role' => 'required|in:admin,driver,user',
            'status' => 'nullable|in:approved,pending,rejected',
        ];

        $requiredDocTypes = DocumentType::where('is_required', true)->get();
        foreach ($requiredDocTypes as $docType) {
            $rules["document_types.{$docType->id}"] = 'required|image|mimes:jpeg,png,jpg,webp|max:5120';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('The Name field is required.'),
            'name.string' => __('The Name must be a string.'),
            'email.email' => __('The Email must be a valid email address.'),
            'email.unique' => __('The Email has already been taken.'),
            'phone.unique' => __('The Phone has already been taken.'),
            'password.required' => __('The Password field is required.'),
            'password.string' => __('The Password must be a string.'),
            'password.min' => __('The Password must be at least 8 characters.'),
            'phone.required' => __('The Phone field is required.'),
            'role.required' => __('The Role field is required.'),
            'activity.in' => __('The Activity must be one of the following: active, inactive.'),
            'wallet.numeric' => __('The Wallet must be a number.'),
            'wallet.min' => __('The Wallet must be at least 0.'),
            'status.in' => __('The Status must be one of the following: approved, pending, rejected.'),
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
