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
            'car_model_id' => 'required|exists:car_models,id',
            'car_category_ids' => 'required|array|min:1',
            'car_category_ids.*' => 'exists:car_categories,id',
            'type_name' => 'required|string|max:255|unique:car_types,type_name',
            'type_year' => 'required|integer',
            'description' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'car_model_id.required' => __('The Car Model/Brand field is required.'),
            'car_model_id.exists' => __('The selected Car Model/Brand is invalid.'),
            'car_category_ids.required' => __('At least one Car Category is required.'),
            'car_category_ids.array' => __('Car Categories must be an array.'),
            'car_category_ids.min' => __('At least one Car Category must be selected.'),
            'car_category_ids.*.exists' => __('One or more selected Car Categories are invalid.'),
            'type_name.required' => __('The Type Name field is required.'),
            'type_name.unique' => __('The Type Name has already been taken.'),
            'type_year.required' => __('The Type Year field is required.'),
            'type_year.integer' => __('The Type Year must be an integer.'),
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
