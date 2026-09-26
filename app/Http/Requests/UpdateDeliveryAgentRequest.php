<?php

namespace App\Http\Requests;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
use App\Enums\VehicleType;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateDeliveryAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('_password_action')) {
            return;
        }

        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : null,
            'zone_id' => $this->filled('zone_id') ? $this->input('zone_id') : null,
            'city_id' => $this->filled('city_id') ? $this->input('city_id') : null,
            'admin_notes' => $this->filled('admin_notes') ? $this->input('admin_notes') : null,
            'rejected_reason' => $this->filled('rejected_reason') ? $this->input('rejected_reason') : null,
        ]);
    }

    public function rules(): array
    {
        if ($this->filled('_password_action')) {
            if ($this->input('_password_action') === 'remove') {
                return [
                    '_password_action' => 'required|in:remove',
                ];
            }

            return [
                '_password_action' => 'required|in:set',
                'rider_password' => 'required|string|min:8|confirmed',
            ];
        }

        $agent = $this->route('delivery_agent');
        $agentId = $agent instanceof User ? $agent->id : $agent;

        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $agentId,
            'phone' => 'required|string|max:30|unique:users,phone,' . $agentId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => ['required', Rule::in(DriverStatus::values())],
            'rejected_reason' => 'nullable|string|max:1000',
            'wallet' => 'required|numeric|min:0',
            'activity' => ['required', Rule::in(ActivtyType::values())],
            'zone_id' => 'nullable|exists:zones,id',
            'city_id' => 'nullable|exists:cities,id',
            'admin_notes' => 'nullable|string|max:5000',
            'vehicle_type' => ['required', Rule::in(VehicleType::values())],
            'rider_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'identity_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'vehicle_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'license_image' => [
                Rule::requiredIf(function () {
                    if ($this->input('vehicle_type') !== VehicleType::Motorcycle->value) {
                        return false;
                    }

                    $agent = $this->route('delivery_agent');
                    if (! $agent instanceof User) {
                        return true;
                    }

                    $agent->loadMissing('riderVehicle');

                    return blank($agent->riderVehicle?->license_image);
                }),
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('The Name field is required.'),
            'email.email' => __('The Email must be a valid email address.'),
            'email.unique' => __('The Email has already been taken.'),
            'phone.required' => __('The Phone field is required.'),
            'phone.unique' => __('The Phone has already been taken.'),
            'rider_password.required' => __('The Password field is required.'),
            'rider_password.min' => __('The Password must be at least 8 characters.'),
            'rider_password.confirmed' => __('The Password confirmation does not match.'),
            'status.in' => __('The Status must be one of the following: approved, rejected, pending.'),
            'status.required' => __('The Status field is required.'),
            'zone_id.exists' => __('The selected zone does not exist.'),
            'city_id.exists' => __('The selected city does not exist.'),
            'activity.in' => __('The Activity must be one of the following: active, inactive, in progress, rejected.'),
            'activity.required' => __('The Activity field is required.'),
            'wallet.numeric' => __('The Wallet must be a number.'),
            'wallet.min' => __('The Wallet must be at least 0.'),
            'wallet.required' => __('The Wallet field is required.'),
            'vehicle_type.required' => __('The Vehicle type field is required.'),
            'vehicle_type.in' => __('The selected vehicle type is invalid.'),
            'license_image.required' => __('A motorcycle license image is required.'),
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new ValidationException($validator, response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ]));
        }

        throw new ValidationException($validator);
    }
}
