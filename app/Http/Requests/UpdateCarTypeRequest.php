<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCarTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'car_category_id' => 'exists:car_categories,id',
            'type_name' => 'nullable|string|max:255|unique:car_types,type_name,' . $this->route('car_type')->id,
            'description' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'car_category_id.exists' => __('The selected Car Category is invalid.'),
            'type_name.unique' => __('The Type Name has already been taken.'),
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
