<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateCancellationReasonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $cancellationReasonId = $this->route('cancellation_reason')
            ? $this->route('cancellation_reason')->id
            : null;

        return [
            'reason' => [
                'nullable',
                'string',
                'max:255',
                // Make reason unique only within the same type, excluding current record
                Rule::unique('cancellation_reasons', 'reason')
                    ->where('type', $this->input('type'))
                    ->ignore($cancellationReasonId)
            ],
            'type' => 'nullable|in:user,driver',
            'is_active' => 'nullable|boolean'
        ];
    }

    public function messages()
    {
        return [
            'reason.unique' => __('This reason already exists for this type. You can use the same reason for a different type.'),
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
