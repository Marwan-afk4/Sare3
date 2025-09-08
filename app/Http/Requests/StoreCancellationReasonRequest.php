<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreCancellationReasonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reason' => 'required|string|max:255|unique:cancellation_reasons,reason',
            'type' => 'required|in:user,driver',
            'is_active' => 'required|boolean'
        ];
    }

    public function messages()
    {
        return [
            'reason.unique' => __('The reason is already exists.'),
            'reason.required' => __('The Reason field is required.'),
            'reason.string' => __('The Reason must be a string.'),
            'reason.max' => __('The Reason may not be greater than 255 characters.'),
            'type.required' => __('The Type field is required.'),
            'type.in' => __('The selected type is invalid. It must be either user or driver.'),
            'is_active.boolean' => __('The is_active field must be true or false.')

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
