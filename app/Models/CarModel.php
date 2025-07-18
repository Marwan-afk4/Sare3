<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    protected $table = 'car_models';

    protected $fillable = [
        'car_categories_id',
        'name'
    ];
    
    public $timestamps = true;

    
    public function carCategories()
    {
        return $this->belongsTo(CarCategory::class);
    }

}
