<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRideRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'exists:users,id',
            'driver_id' => 'exists:drivers,id',
            'car_category_id' => 'exists:car_categories,id',
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'pickup_address' => 'required',
            'dropoff_lat' => 'required',
            'dropoff_lng' => 'required',
            'dropoff_address' => 'required',
            'status' => 'required',
            'estimated_km' => 'required',
            'estimated_time' => 'required',
            'calculated_initial_price' => 'required',
            'route_points' => 'required',
            'calculated_final_price' => 'required',
            'started_at' => 'required',
            'ended_at' => 'required',
            'time_taken' => 'required',
            'firebase_ride_id' => 'exists:firebase_rides,id'
        ];
    }

    public function messages()
    {
        return [
            'user_id.exists' => __('The selected User is invalid.'),
            'driver_id.exists' => __('The selected Driver is invalid.'),
            'car_category_id.exists' => __('The selected Car Category is invalid.'),
            'pickup_lat.required' => __('The Pickup Lat field is required.'),
            'pickup_lng.required' => __('The Pickup Lng field is required.'),
            'pickup_address.required' => __('The Pickup Address field is required.'),
            'dropoff_lat.required' => __('The Dropoff Lat field is required.'),
            'dropoff_lng.required' => __('The Dropoff Lng field is required.'),
            'dropoff_address.required' => __('The Dropoff Address field is required.'),
            'status.required' => __('The Status field is required.'),
            'estimated_km.required' => __('The Estimated Km field is required.'),
            'estimated_time.required' => __('The Estimated Time field is required.'),
            'calculated_initial_price.required' => __('The Calculated Initial Price field is required.'),
            'route_points.required' => __('The Route Points field is required.'),
            'calculated_final_price.required' => __('The Calculated Final Price field is required.'),
            'started_at.required' => __('The Started At field is required.'),
            'ended_at.required' => __('The Ended At field is required.'),
            'time_taken.required' => __('The Time Taken field is required.'),
            'firebase_ride_id.exists' => __('The selected Firebase Ride is invalid.')
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
