<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRatingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rater_id' => 'exists:users,id',
            'ratee_id' => 'exists:users,id',
            'rate' => 'nullable',
            'comment' => 'nullable',
            'ratee_type' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'rater_id.exists' => __('The selected Rater is invalid.'),
            'ratee_id.exists' => __('The selected Ratee is invalid.'),
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
