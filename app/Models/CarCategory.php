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
        'price_per_km'
    ];
    
    public $timestamps = true;

    
}
