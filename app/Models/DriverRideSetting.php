<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRideSetting extends Model
{
    use HasFactory;

    protected $table = 'driver_ride_settings';

    protected $fillable = [
        'driver_id',
        'pickup_radius',
        'destination_preferences',
        'early_trip_suggestions',
        'same_gender_trips',
        'zone_id'
    ];

    public $timestamps = true;

    protected $casts = [
        'destination_preferences' => 'array',
    ];


    public function driver()
    {
        return $this->belongsTo(User::class);
    }

}
