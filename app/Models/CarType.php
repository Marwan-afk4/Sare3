<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarType extends Model
{
    use HasFactory;

    protected $table = 'car_types';

    protected $fillable = [
        'car_category_id',
        'type_name',
        'description'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];


    public function carCategory()
    {
        return $this->belongsTo(CarCategory::class, 'car_category_id');
    }

    public function driverCars()
    {
        return $this->hasMany(DriverCar::class, 'car_type_id');
    }

}
