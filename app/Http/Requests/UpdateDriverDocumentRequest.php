<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'driver_id' => 'exists:users,id',
            'identity_number' => 'nullable',
            'selfi_image' => 'nullable',
            'face_identity' => 'nullable',
            'back_identity' => 'nullable',
            'driving_license' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'driver_id.exists' => __('The selected Driver is invalid.'),
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
