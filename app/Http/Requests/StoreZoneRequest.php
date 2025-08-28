<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|unique:zones,name',
            'from_lat' => 'required',
            'from_lng' => 'required',
            'to_lat' => 'required',
            'to_lng' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('The Name field is required.'),
            'from_lat.required' => __('The From Lat field is required.'),
            'from_lng.required' => __('The From Lng field is required.'),
            'to_lat.required' => __('The To Lat field is required.'),
            'to_lng.required' => __('The To Lng field is required.')
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
