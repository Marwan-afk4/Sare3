<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideEstimate extends Model
{
    use HasFactory;

    protected $table = 'ride_estimates';

    protected $fillable = [
        'user_id',
        'car_category_id',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'estimated_km',
        'estimated_time',
        'calculated_price'
    ];
    
    public $timestamps = true;

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carCategory()
    {
        return $this->belongsTo(CarCategory::class);
    }

}
