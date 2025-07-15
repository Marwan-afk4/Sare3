<?php

namespace App\Models;

use App\Enums\RideStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $table = 'rides';

    protected $fillable = [
        'user_id',
        'driver_id',
        'car_category_id',
        'pickup_lat',
        'pickup_lng',
        'pickup_address',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_address',
        'status',
        'estimated_km',
        'estimated_time',
        'calculated_initial_price',
        'route_points',
        'calculated_final_price',
        'started_at',
        'ended_at',
        'time_taken',
        'firebase_ride_id'
    ];

    public $timestamps = true;

    protected $casts = [
        'route_points' => 'array',
        'status' => RideStatus::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function carCategory()
    {
        return $this->belongsTo(CarCategory::class);
    }


}
