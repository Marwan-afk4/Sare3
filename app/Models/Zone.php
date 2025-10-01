<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'from_lat',
        'from_lng',
        'to_lat',
        'to_lng',
        'polygon_coordinates'
    ];

    protected $casts = [
        'polygon_coordinates' => 'array'
    ];

    public $timestamps = true;


    public function carCategories()
    {
        return $this->belongsToMany(CarCategory::class, 'car_category_zone')
                    ->withPivot(['price_per_km', 'price_per_min', 'base_price', 'min_price'])
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }

}
