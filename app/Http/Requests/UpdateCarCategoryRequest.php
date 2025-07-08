<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCarCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|unique:car_categories,name,' . $this->route('car_category')->id,
            'description' => 'nullable|string',
            'icon' => 'nullable',
            'inital_price' => 'nullable',
            'final_price' => 'nullable',
            'price_per_km' => 'nullable|numeric|min:0',
            'price_per_time' => 'nullable|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'name.string'=> __('The Name field must be a string.'),
            'name.unique'=> __('The Name field must be unique.'),
            'description.string'=> __('The Description field must be a string.'),
            'price_per_km.numeric'=> __('The Price Per Km field must be numeric.'),
            'price_per_km.min'=> __('The Price Per Km field must be greater than or equal to 0.'),
            'price_per_time.numeric'=> __('The Price Per Time field must be numeric.'),
            'price_per_time.min'=> __('The Price Per Time field must be greater than or equal to 0.'),
            'base_price.numeric'=> __('The Base Price field must be numeric.'),
            'base_price.min'=> __('The Base Price field must be greater than or equal to 0.'),
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
