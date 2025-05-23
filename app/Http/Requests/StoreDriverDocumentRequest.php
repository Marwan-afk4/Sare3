<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreDriverDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'driver_id' => 'exists:users,id',
            'identity_number' => 'required',
            'selfi_image' => 'required',
            'face_identity' => 'nullable',
            'back_identity' => 'nullable',
            'driving_license' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'driver_id.exists' => __('The selected Driver is invalid.'),
            'identity_number.required' => __('The Identity Number field is required.'),
            'selfi_image.required' => __('The Selfi Image field is required.'),
            'face_identity.exists' => __('The selected Faceentity is invalid.'),
            'back_identity.exists' => __('The selected Backentity is invalid.'),
            'driving_license.required' => __('The Driving License field is required.')
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
