<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarType extends Model
{
    use HasFactory;

    protected $table = 'car_types';

    protected $fillable = [
        'car_brand_id',
        'type_name',
        'description'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];


    public function carBrand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function carCategories()
    {
        return $this->belongsToMany(CarCategory::class, 'car_category_car_type', 'car_type_id', 'car_category_id')
                    ->withTimestamps();
    }

    public function driverCars()
    {
        return $this->hasMany(DriverCar::class, 'car_type_id');
    }

}
