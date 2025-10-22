<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    protected $table = 'car_models';

    protected $fillable = [
        'name'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function carTypes()
    {
        return $this->hasMany(CarType::class, 'car_model_id');
    }

}
