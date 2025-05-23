<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
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
            'rate' => 'required',
            'comment' => 'required',
            'ratee_type' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'rater_id.exists' => __('The selected Rater is invalid.'),
            'ratee_id.exists' => __('The selected Ratee is invalid.'),
            'rate.required' => __('The Rate field is required.'),
            'comment.required' => __('The Comment field is required.'),
            'ratee_type.required' => __('The Ratee Type field is required.')
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
