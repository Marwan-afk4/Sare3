<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCancellationReasonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reason' => 'nullable|string|max:255|unique:cancellation_reasons,reason,' . $this->route('cancellation_reason')->id,
            'type' => 'nullable|in:user,driver',
            'is_active' => 'nullable|boolean'
        ];
    }

    public function messages()
    {
        return [
            'reason.unique' => __('The reason is already exists.'),
            'reason.string' => __('The Reason must be a string.'),
            'reason.max' => __('The Reason may not be greater than 255 characters.'),
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
