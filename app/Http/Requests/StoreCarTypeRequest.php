<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreCarTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'car_category_id' => 'exists:car_categories,id',
            'type_name' => 'required',
            'description' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'car_category_id.exists' => __('The selected Car Category is invalid.'),
            'type_name.required' => __('The Type Name field is required.'),
            'description.required' => __('The Description field is required.')
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
