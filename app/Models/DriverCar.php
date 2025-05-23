<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCar extends Model
{
    use HasFactory;

    protected $table = 'driver_cars';

    protected $fillable = [
        'driver_id',
        'car_image',
        'car_type',
        'car_number',
        'car_color',
        'car_category',
        'car_license'
    ];

    public $timestamps = true;


    public function driver()
    {
        return $this->belongsTo(User::class);
    }

}
