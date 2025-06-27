<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarCategory extends Model
{
    use HasFactory;

    protected $table = 'car_categories';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'inital_price',
        'final_price',
        'price_per_km',
        'price_per_time',
        'base_price'
    ];

    public $timestamps = true;


    public $appends = [
        'icon_url'
    ];

    public function getIconUrlAttribute()
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }

    public function rideEstimate()
    {
        return $this->hasMany(RideEstimate::class);
    }
}
