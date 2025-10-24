<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWalletRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'driver_id' => 'exists:drivers,id',
            'amount' => 'nullable|numeric|min:1',
            'type' => 'nullable|in:withdraw,deposit',
            'status' => 'nullable|in:pending,approved,rejected',
            'note' => 'nullable|string',
            'admin_message' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'driver_id.exists' => __('The driver does not exist.'),
            'amount.numeric' => __('The amount must be a number.'),
            'amount.min' => __('The amount must be at least 1.'),
            'type.in' => __('The type must be either withdraw or deposit.'),
            'status.in' => __('The status must be either pending, approved, or rejected.'),
            'note.string' => __('The note must be a string.'),
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
