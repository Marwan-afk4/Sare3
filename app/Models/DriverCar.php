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
        'car_categories_id',
        'car_image',
        'car_type_id',
        'car_number',
        'car_color',
        'car_license',
        'car_model_id'
    ];

    public $timestamps = true;

    protected $appends = [
        'car_image_link',
        'car_license_link'
    ];

    public function getCarImageLinkAttribute()
    {
        return $this->car_image ? asset('storage/' . $this->car_image) : null;
    }

    public function getCarLicenseLinkAttribute()
    {
        return $this->car_license ? asset('storage/' . $this->car_license) : null;
    }


    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function carCategory()
    {
        return $this->belongsTo(CarCategory::class, 'car_categories_id');
    }

	public function carCategories()
	{
		return $this->belongsToMany(CarCategory::class, 'car_category_driver_car', 'driver_car_id', 'car_category_id')
			->withTimestamps();
	}

    public function carType()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

}
