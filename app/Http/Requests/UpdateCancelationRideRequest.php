<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCancelationRideRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ride_id' => 'exists:rides,id',
            'user_id' => 'exists:users,id',
            'driver_id' => 'exists:drivers,id',
            'cancelation_policy_id' => 'exists:cancelation_policies,id',
            'canceled_by' => 'required',
            'penalty_applied' => 'required',
            'penalty_amount' => 'required',
            'canceled_at' => 'required',
            'reason' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'ride_id.exists' => __('The selected Ride is invalid.'),
            'user_id.exists' => __('The selected User is invalid.'),
            'driver_id.exists' => __('The selected Driver is invalid.'),
            'cancelation_policy_id.exists' => __('The selected Cancelation Policy is invalid.'),
            'canceled_by.required' => __('The Canceled By field is required.'),
            'penalty_applied.required' => __('The Penalty Applied field is required.'),
            'penalty_amount.required' => __('The Penalty Amount field is required.'),
            'canceled_at.required' => __('The Canceled At field is required.'),
            'reason.required' => __('The Reason field is required.'),
            'reason.string' => __('The Reason must be a string.')
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
