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
            'car_model_id' => 'required|exists:car_models,id',
            'car_category_ids' => 'required|array|min:1',
            'car_category_ids.*' => 'exists:car_categories,id',
            'type_name' => 'nullable|string|max:255|unique:car_types,type_name,' . $this->route('car_type')->id,
            'year_from' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'year_to' => 'nullable|integer|min:1900|max:' . (date('Y') + 10) . '|gte:year_from',
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
            'type_name.unique' => __('The Type Name has already been taken.'),
            'year_from.integer' => __('The Year From must be an integer.'),
            'year_from.min' => __('The Year From must be at least 1900.'),
            'year_from.max' => __('The Year From cannot be more than :max.', ['max' => date('Y') + 10]),
            'year_to.integer' => __('The Year To must be an integer.'),
            'year_to.min' => __('The Year To must be at least 1900.'),
            'year_to.max' => __('The Year To cannot be more than :max.', ['max' => date('Y') + 10]),
            'year_to.gte' => __('The Year To must be greater than or equal to Year From.'),
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
