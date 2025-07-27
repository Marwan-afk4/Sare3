<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymenentMethodRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255|unique:paymenent_methods,name,' . $this->route('paymenent_method')->id,
            'status' => 'nullable|in:active,inactive', // Assuming 'active' and 'inactive' are the valid statuses
        ];
    }

    public function messages()
    {
        return [
            'name.string' => __('The Name must be a string.'),
            'name.max' => __('The Name may not be greater than 255 characters.'),
            'name.unique' => __('The Name has already been taken.'),
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
