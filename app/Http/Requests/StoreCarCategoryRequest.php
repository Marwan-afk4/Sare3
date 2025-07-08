<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreCarCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:car_categories,name',
            'description' => 'required|string',
            'icon' => 'nullable',
            'inital_price' => 'nullable',
            'final_price' => 'nullable',
            'price_per_km' => 'required|numeric|min:0',
            'price_per_time' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'name.required'=> __('The Name field is required.'),
            'name.string'=> __('The Name field must be a string.'),
            'name.unique'=> __('The Name field must be unique.'),
            'description.required'=> __('The Description field is required.'),
            'description.string'=> __('The Description field must be a string.'),
            'price_per_km.required'=> __('The Price Per Km field is required.'),
            'price_per_km.numeric'=> __('The Price Per Km field must be numeric.'),
            'price_per_km.min'=> __('The Price Per Km field must be greater than or equal to 0.'),
            'price_per_time.required'=> __('The Price Per Time field is required.'),
            'price_per_time.numeric'=> __('The Price Per Time field must be numeric.'),
            'price_per_time.min'=> __('The Price Per Time field must be greater than or equal to 0.'),
            'base_price.required'=> __('The Base Price field is required.'),
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
