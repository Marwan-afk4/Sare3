<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|unique:zones,name,'.$this->route('zone')->id,
            'from_lat' => 'nullable',
            'from_lng' => 'nullable',
            'to_lat' => 'nullable',
            'to_lng' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => __('The Name has already been taken.'),
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
