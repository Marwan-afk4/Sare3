<?php

namespace App\Http\Requests;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
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
        if ($this->filled('_password_action')) {
            if ($this->input('_password_action') === 'remove') {
                return [
                    '_password_action' => 'required|in:remove',
                ];
            }

            return [
                '_password_action' => 'required|in:set',
                'driver_password' => 'required|string|min:8|confirmed',
            ];
        }

        $driver = $this->route('driver');
        $driverId = $driver instanceof \App\Models\User ? $driver->id : $driver;

        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $driverId,
            'phone' => 'nullable|unique:users,phone,' . $driverId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => ['nullable', Rule::in(DriverStatus::values())],
            'wallet' => 'nullable|numeric|min:0',
            'activity' => ['required', Rule::in(ActivtyType::values())],
            'zone_id' => 'nullable|exists:zones,id',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => __('The Email must be a valid email address.'),
            'email.unique' => __('The Email has already been taken.'),
            'phone.unique' => __('The Phone has already been taken.'),
            'driver_password.required' => __('The Password field is required.'),
            'driver_password.min' => __('The Password must be at least 8 characters.'),
            'driver_password.confirmed' => __('The Password confirmation does not match.'),
            'status.in' => __('The Status must be one of the following: approved, rejected, pending.'),
            'zone_id.exists' => __('The selected zone does not exist.'),
            'activity.in'=> __('The Activity must be one of the following: active, inactive, in progress, rejected.'),
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
