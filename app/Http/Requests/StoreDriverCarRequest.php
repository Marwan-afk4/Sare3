<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreDriverCarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'driver_id' => 'required|exists:users,id',
            'car_categories_id' => 'required|exists:car_categories,id',
            'car_model_id' => 'required|exists:car_models,id',
            'car_type_id' => 'required|exists:car_types,id',
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'car_number' => 'required|string|max:255',
            'car_color' => 'required|string|max:255',
            'car_license' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'driver_id.required' => __('The Driver field is required.'),
            'driver_id.exists' => __('The selected Driver is invalid.'),
            'car_categories_id.required' => __('The Car Category field is required.'),
            'car_categories_id.exists' => __('The selected Car Category is invalid.'),
            'car_model_id.required' => __('The Car Model field is required.'),
            'car_model_id.exists' => __('The selected Car Model is invalid.'),
            'car_type_id.required' => __('The Car Type field is required.'),
            'car_type_id.exists' => __('The selected Car Type is invalid.'),
            'car_number.required' => __('The Car Number field is required.'),
            'car_color.required' => __('The Car Color field is required.'),
            'car_image.image' => __('The Car Image must be an image file.'),
            'car_image.mimes' => __('The Car Image must be a file of type: jpeg, png, jpg, gif.'),
            'car_image.max' => __('The Car Image may not be greater than 2MB.'),
            'car_license.image' => __('The Car License must be an image file.'),
            'car_license.mimes' => __('The Car License must be a file of type: jpeg, png, jpg, gif.'),
            'car_license.max' => __('The Car License may not be greater than 2MB.'),
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
