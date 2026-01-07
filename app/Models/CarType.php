<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarType extends Model
{
    use HasFactory;

    protected $table = 'car_types';

    protected $fillable = [
        'car_model_id',
        'type_name',
        'year_from',
        'year_to',
        'description'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];


    public function carModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
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

    /**
     * Get the formatted year range for display
     */
    public function getYearRangeAttribute()
    {
        if ($this->year_from && $this->year_to) {
            return $this->year_from . ' - ' . $this->year_to;
        } elseif ($this->year_from) {
            return $this->year_from . '+';
        } elseif ($this->year_to) {
            return 'Up to ' . $this->year_to;
        }

        return '-';
    }

}
