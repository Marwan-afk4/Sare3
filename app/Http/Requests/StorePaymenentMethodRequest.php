<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymenentMethodRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:paymenent_methods,name',
            'status' => 'required|in:active,inactive', // Assuming 'active' and 'inactive' are the valid statuses
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('The Name field is required.'),
            'name.string' => __('The Name must be a string.'),
            'name.max' => __('The Name may not be greater than 255 characters.'),
            'name.unique' => __('The Name has already been taken.'),
            'status.required' => __('The Status field is required.'),
            'status.in' => __('The selected Status is invalid.'),
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
