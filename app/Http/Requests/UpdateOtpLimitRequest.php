<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOtpLimitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'nullable|in:user,driver|unique:otp_limits,type,' . $this->route('otp_limit')->id,
            'otp_limit' => 'nullable|integer|min:1|max:10',
        ];
    }

    public function messages()
    {
        return [
            'type.in' => __('The selected type is invalid.'),
            'type.unique' => __('The type has already been taken.'),
            'otp_limit.integer' => __('The OTP limit must be an integer.'),
            'otp_limit.min' => __('The OTP limit must be at least :min.'),
            'otp_limit.max' => __('The OTP limit may not be greater than :max.'),
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
