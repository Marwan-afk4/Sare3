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
            'driver_id' => 'exists:users,id',
            'car_image' => 'required',
            'car_type' => 'required',
            'car_number' => 'required',
            'car_color' => 'required',
            'car_category' => 'required',
            'car_license' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'driver_id.exists' => __('The selected Driver is invalid.'),
            'car_image.required' => __('The Car Image field is required.'),
            'car_type.required' => __('The Car Type field is required.'),
            'car_number.required' => __('The Car Number field is required.'),
            'car_color.required' => __('The Car Color field is required.'),
            'car_category.required' => __('The Car Category field is required.'),
            'car_license.required' => __('The Car License field is required.')
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
