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
            'driver_id' => 'required|exists:users,id',
            'document_type_id' => 'required|exists:document_types,id',
            'document_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'driver_id.required' => __('The Driver field is required.'),
            'driver_id.exists' => __('The selected Driver is invalid.'),
            'document_type_id.required' => __('The Document Type field is required.'),
            'document_type_id.exists' => __('The selected Document Type is invalid.'),
            'document_file.image' => __('The file must be an image.'),
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
