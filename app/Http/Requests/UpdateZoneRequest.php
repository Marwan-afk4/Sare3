<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|unique:zones,name,'.$this->route('zone')->id,
            'from_lat' => 'nullable|numeric|between:-90,90',
            'from_lng' => 'nullable|numeric|between:-180,180',
            'to_lat' => 'nullable|numeric|between:-90,90',
            'to_lng' => 'nullable|numeric|between:-180,180',
            'polygon_coordinates' => 'nullable|json'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasCoordinates = $this->filled(['from_lat', 'from_lng', 'to_lat', 'to_lng']);
            $hasPolygon = $this->filled('polygon_coordinates') && $this->polygon_coordinates !== '[]';
            
            if (!$hasCoordinates && !$hasPolygon) {
                $validator->errors()->add('coordinates', __('Either provide manual coordinates or draw a polygon on the map.'));
            }
        });
    }

    public function messages()
    {
        return [
            'name.unique' => __('The Name has already been taken.'),
            'from_lat.numeric' => __('The From Lat must be a valid number.'),
            'from_lng.numeric' => __('The From Lng must be a valid number.'),
            'to_lat.numeric' => __('The To Lat must be a valid number.'),
            'to_lng.numeric' => __('The To Lng must be a valid number.'),
            'polygon_coordinates.json' => __('The polygon coordinates must be valid JSON.')
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
